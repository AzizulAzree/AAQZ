#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use reqwest::{Client, StatusCode, Url};
use serde::{Deserialize, Serialize};
use std::time::Duration;
use tauri::{Manager, PhysicalPosition, PhysicalSize, State};

// The only server setting. Override API_BASE_URL before launching for HTTPS/local testing.
const DEFAULT_API_BASE_URL: &str = "http://35.185.197.15";
struct Api {
    client: Client,
    base: String,
}

#[derive(Deserialize)]
struct Session {
    csrf_token: String,
}
#[derive(Deserialize, Serialize)]
struct CalendarEvent {
    id: serde_json::Value,
    title: String,
    date: String,
}
#[derive(Deserialize, Serialize)]
struct CalendarResponse {
    events: Vec<CalendarEvent>,
}

#[tauri::command]
async fn login(api: State<'_, Api>, email: String, password: String) -> Result<(), String> {
    let session = api
        .client
        .get(format!("{}/api/widget/session", api.base))
        .send()
        .await
        .map_err(|_| "connection")?
        .error_for_status()
        .map_err(|_| "connection")?
        .json::<Session>()
        .await
        .map_err(|_| "connection")?;
    let response = api
        .client
        .post(format!("{}/api/widget/login", api.base))
        .header("X-CSRF-TOKEN", session.csrf_token)
        .json(&serde_json::json!({ "email": email, "password": password }))
        .send()
        .await
        .map_err(|_| "connection")?;
    match response.status() {
        StatusCode::OK => Ok(()),
        StatusCode::UNPROCESSABLE_ENTITY | StatusCode::TOO_MANY_REQUESTS => {
            Err("invalid_credentials".into())
        }
        _ => Err("connection".into()),
    }
}

#[tauri::command]
async fn calendar(api: State<'_, Api>, month: String) -> Result<CalendarResponse, String> {
    let response = api
        .client
        .get(format!("{}/api/widget/calendar", api.base))
        .query(&[("month", month)])
        .send()
        .await
        .map_err(|_| "connection")?;
    if response.status() == StatusCode::UNAUTHORIZED {
        return Err("unauthenticated".into());
    }
    response
        .error_for_status()
        .map_err(|_| "connection")?
        .json()
        .await
        .map_err(|_| "connection".into())
}

fn position(window: &tauri::WebviewWindow, expanded: bool) -> tauri::Result<()> {
    if let Some(monitor) = window.primary_monitor()? {
        let scale = monitor.scale_factor();
        let width = ((if expanded { 336.0 } else { 128.0 }) * scale).round() as u32;
        let height = ((if expanded { 516.0 } else { 52.0 }) * scale).round() as u32;
        window.set_size(PhysicalSize::new(width, height))?;
        window.set_position(PhysicalPosition::new(
            monitor.position().x + (monitor.size().width as i32 - width as i32) / 2,
            monitor.position().y + (8.0 * scale).round() as i32,
        ))?;
    }
    Ok(())
}

#[tauri::command]
fn resize_widget(window: tauri::WebviewWindow, expanded: bool) -> Result<(), String> {
    position(&window, expanded).map_err(|_| "Unable to resize widget".into())
}

fn main() {
    tauri::Builder::default()
        .setup(|app| {
            let base =
                std::env::var("API_BASE_URL").unwrap_or_else(|_| DEFAULT_API_BASE_URL.into());
            let url = Url::parse(&base)?;
            if !["http", "https"].contains(&url.scheme())
                || url.host_str().is_none()
                || !url.username().is_empty()
                || url.password().is_some()
                || url.query().is_some()
                || url.fragment().is_some()
            {
                return Err(
                    "API_BASE_URL must be an HTTP(S) URL without credentials, query or fragment"
                        .into(),
                );
            }
            let mut headers = reqwest::header::HeaderMap::new();
            headers.insert(reqwest::header::ACCEPT, "application/json".parse()?);
            app.manage(Api {
                base: base.trim_end_matches('/').into(),
                client: Client::builder()
                    .cookie_store(true)
                    .default_headers(headers)
                    .timeout(Duration::from_secs(15))
                    .redirect(reqwest::redirect::Policy::none())
                    .build()?,
            });
            let window = app
                .get_webview_window("main")
                .ok_or("Main window missing")?;
            position(&window, false)?;
            window.show()?;
            Ok(())
        })
        .invoke_handler(tauri::generate_handler![calendar, login, resize_widget])
        .run(tauri::generate_context!())
        .expect("Unable to start AAQZ Calendar");
}
