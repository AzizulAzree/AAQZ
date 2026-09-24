use reqwest::{Client, StatusCode, Url};
use serde::{Deserialize, Serialize};
use serde_json::{json, Value};
use std::time::Duration;
use tauri::State;
use tauri_plugin_opener::OpenerExt;
use tokio::sync::Mutex;

#[derive(Clone, Deserialize, Serialize)]
pub struct Account {
    pub id: u64,
    pub name: String,
    pub email: String,
}
#[derive(Deserialize, Serialize)]
struct SavedAccount {
    account: Account,
    token: String,
}
pub struct Session {
    client: Client,
    account: Option<Account>,
}
pub struct Api {
    pub base: String,
    session: Mutex<Session>,
}

fn client() -> Result<Client, String> {
    let mut headers = reqwest::header::HeaderMap::new();
    headers.insert(reqwest::header::ACCEPT, "application/json".parse().unwrap());
    Client::builder()
        .cookie_store(true)
        .default_headers(headers)
        .timeout(Duration::from_secs(15))
        .redirect(reqwest::redirect::Policy::none())
        .build()
        .map_err(|_| "connection".into())
}

impl Api {
    pub fn new(base: String) -> Result<Self, String> {
        Ok(Self {
            base,
            session: Mutex::new(Session {
                client: client()?,
                account: None,
            }),
        })
    }
    fn entry(&self, id: &str) -> Result<keyring::Entry, String> {
        keyring::Entry::new(&format!("AAQZ Widget:{}", self.base), id).map_err(|_| "storage".into())
    }
    fn ids(&self) -> Result<Vec<u64>, String> {
        match self.entry("accounts")?.get_password() {
            Ok(value) => serde_json::from_str(&value).map_err(|_| "storage".into()),
            Err(keyring::Error::NoEntry) => Ok(vec![]),
            Err(_) => Err("storage".into()),
        }
    }
    fn saved(&self, id: u64) -> Result<SavedAccount, String> {
        let value = self
            .entry(&id.to_string())?
            .get_password()
            .map_err(|_| "storage")?;
        serde_json::from_str(&value).map_err(|_| "storage".into())
    }
    fn save(&self, saved: &SavedAccount) -> Result<(), String> {
        let mut ids = self.ids()?;
        let new = !ids.contains(&saved.account.id);
        if new && ids.len() >= 10 {
            return Err("account_limit".into());
        }
        self.entry(&saved.account.id.to_string())?
            .set_password(&serde_json::to_string(saved).map_err(|_| "storage")?)
            .map_err(|_| "storage")?;
        if new {
            ids.push(saved.account.id);
        }
        if self
            .entry("accounts")?
            .set_password(&serde_json::to_string(&ids).unwrap())
            .is_err()
        {
            if new {
                let _ = self
                    .entry(&saved.account.id.to_string())?
                    .delete_credential();
            }
            return Err("storage".into());
        }
        Ok(())
    }
    fn remove(&self, id: u64) -> Result<(), String> {
        match self.entry(&id.to_string())?.delete_credential() {
            Ok(()) | Err(keyring::Error::NoEntry) => {}
            Err(_) => return Err("storage".into()),
        }
        let ids: Vec<_> = self
            .ids()?
            .into_iter()
            .filter(|value| *value != id)
            .collect();
        self.entry("accounts")?
            .set_password(&serde_json::to_string(&ids).unwrap())
            .map_err(|_| "storage".into())
    }
    async fn post(&self, client: &Client, path: &str, body: Value) -> Result<Value, String> {
        let session: Value = decode(
            client
                .get(format!("{}/api/widget/session", self.base))
                .send()
                .await
                .map_err(|_| "connection")?,
        )
        .await?;
        let csrf = session["csrf_token"].as_str().ok_or("connection")?;
        decode(
            client
                .post(format!("{}/api/widget/{path}", self.base))
                .header("X-CSRF-TOKEN", csrf)
                .json(&body)
                .send()
                .await
                .map_err(|_| "connection")?,
        )
        .await
    }
}

async fn decode(response: reqwest::Response) -> Result<Value, String> {
    match response.status() {
        StatusCode::UNAUTHORIZED => return Err("unauthenticated".into()),
        StatusCode::UNPROCESSABLE_ENTITY | StatusCode::TOO_MANY_REQUESTS => {
            return Err("invalid_credentials".into())
        }
        _ => {}
    }
    response
        .error_for_status()
        .map_err(|_| "connection")?
        .json()
        .await
        .map_err(|_| "connection".into())
}

#[tauri::command]
pub async fn account_state(api: State<'_, Api>) -> Result<Value, String> {
    let session = api.session.lock().await;
    let saved: Vec<Account> = api
        .ids()?
        .into_iter()
        .filter_map(|id| api.saved(id).ok().map(|s| s.account))
        .collect();
    Ok(json!({ "account": session.account, "saved": saved }))
}

#[tauri::command]
pub async fn login(
    api: State<'_, Api>,
    email: String,
    password: String,
    save_account: bool,
) -> Result<Account, String> {
    let mut session = api.session.lock().await;
    *session = Session {
        client: client()?,
        account: None,
    };
    let result = api
        .post(
            &session.client,
            "login",
            json!({"email": email, "password": password, "save_account": save_account}),
        )
        .await?;
    let account: Account =
        serde_json::from_value(result["account"].clone()).map_err(|_| "connection")?;
    if save_account {
        let token = result["token"].as_str().ok_or("connection")?.to_string();
        let old = api.saved(account.id).ok();
        if let Err(error) = api.save(&SavedAccount {
            account: account.clone(),
            token: token.clone(),
        }) {
            let _ = api
                .post(&session.client, "forget", json!({"token": token}))
                .await;
            let _ = api.post(&session.client, "logout", json!({})).await;
            *session = Session {
                client: client()?,
                account: None,
            };
            return Err(error);
        }
        if let Some(old) = old {
            let _ = api
                .post(&session.client, "forget", json!({"token": old.token}))
                .await;
        }
    }
    session.account = Some(account.clone());
    Ok(account)
}

#[tauri::command]
pub async fn resume_account(api: State<'_, Api>, id: u64) -> Result<Account, String> {
    let mut session = api.session.lock().await;
    *session = Session {
        client: client()?,
        account: None,
    };
    let saved = api.saved(id)?;
    let result = api
        .post(&session.client, "resume", json!({"token": saved.token}))
        .await?;
    let account: Account =
        serde_json::from_value(result["account"].clone()).map_err(|_| "connection")?;
    if account.id != id {
        return Err("unauthenticated".into());
    }
    session.account = Some(account.clone());
    Ok(account)
}

#[tauri::command]
pub async fn switch_account(api: State<'_, Api>) -> Result<(), String> {
    let mut session = api.session.lock().await;
    let old = std::mem::replace(
        &mut *session,
        Session {
            client: client()?,
            account: None,
        },
    );
    if old.account.is_some() {
        let _ = api.post(&old.client, "logout", json!({})).await;
    }
    Ok(())
}

#[tauri::command]
pub async fn remove_account(api: State<'_, Api>, id: u64) -> Result<bool, String> {
    let session = api.session.lock().await;
    let saved = api.saved(id)?;
    let revoked = api
        .post(&session.client, "forget", json!({"token": saved.token}))
        .await
        .is_ok();
    api.remove(id)?;
    Ok(revoked)
}

async fn get(api: &Api, path: &str) -> Result<Value, String> {
    let mut session = api.session.lock().await;
    if session.account.is_none() {
        return Err("unauthenticated".into());
    }
    let result = decode(
        session
            .client
            .get(format!("{}{path}", api.base))
            .send()
            .await
            .map_err(|_| "connection")?,
    )
    .await;
    if result
        .as_ref()
        .err()
        .is_some_and(|e| e == "unauthenticated")
    {
        session.account = None;
    }
    result
}

#[tauri::command]
pub async fn calendar(api: State<'_, Api>, month: String) -> Result<Value, String> {
    let mut url = Url::parse("http://localhost/api/widget/calendar").unwrap();
    url.query_pairs_mut().append_pair("month", &month);
    get(&api, &format!("{}?{}", url.path(), url.query().unwrap())).await
}
#[tauri::command]
pub async fn workspace(api: State<'_, Api>) -> Result<Value, String> {
    get(&api, "/api/widget/workspace").await
}
#[tauri::command]
pub async fn note(api: State<'_, Api>, id: u64) -> Result<Value, String> {
    get(&api, &format!("/api/widget/notes/{id}")).await
}

fn safe_url(value: &str) -> Result<Url, String> {
    let url = Url::parse(value).map_err(|_| "unsafe_url")?;
    if !["https", "http"].contains(&url.scheme())
        || url.host_str().is_none()
        || !url.username().is_empty()
        || url.password().is_some()
    {
        return Err("unsafe_url".into());
    }
    Ok(url)
}
#[tauri::command]
pub fn open_shortcut(app: tauri::AppHandle, url: String) -> Result<(), String> {
    app.opener()
        .open_url(safe_url(&url)?.as_str(), None::<&str>)
        .map_err(|_| "open_failed".into())
}

#[cfg(test)]
mod tests {
    use super::*;
    #[test]
    fn windows_saved_accounts_survive_restart_and_removal_is_scoped() {
        let unique = std::time::SystemTime::now()
            .duration_since(std::time::UNIX_EPOCH)
            .unwrap()
            .as_nanos();
        let base = format!("https://credential-test-{unique}.invalid");
        let api = Api::new(base.clone()).unwrap();
        for id in [1, 2] {
            api.save(&SavedAccount {
                account: Account {
                    id,
                    name: format!("Test {id}"),
                    email: format!("test{id}@example.invalid"),
                },
                token: format!("test-only-token-{id}"),
            })
            .unwrap();
        }
        let reopened = Api::new(base).unwrap();
        assert_eq!(reopened.ids().unwrap(), vec![1, 2]);
        assert_eq!(reopened.saved(1).unwrap().token, "test-only-token-1");
        assert!(Api::new(format!("{}-other", reopened.base))
            .unwrap()
            .ids()
            .unwrap()
            .is_empty());
        reopened.remove(1).unwrap();
        assert!(reopened.saved(1).is_err());
        assert_eq!(reopened.saved(2).unwrap().account.id, 2);
        reopened.remove(2).unwrap();
        reopened
            .entry("accounts")
            .unwrap()
            .delete_credential()
            .unwrap();
    }
    #[test]
    fn shortcuts_only_allow_web_urls_without_credentials() {
        for url in [
            "file:///C:/Windows/system32/cmd.exe",
            "javascript:alert(1)",
            "ms-settings:privacy",
            "https://user:pass@example.com",
            "invalid",
        ] {
            assert!(safe_url(url).is_err());
        }
        assert!(safe_url("https://example.com/docs?q=one").is_ok());
    }
}
