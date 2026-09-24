#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use reqwest::Url;
mod api;
use api::*;

use tauri::menu::{Menu, MenuItem};
use tauri::tray::{MouseButton, MouseButtonState, TrayIconBuilder, TrayIconEvent};
use tauri::{Manager, PhysicalPosition, PhysicalSize};

fn show_widget(app: &tauri::AppHandle) {
    if let Some(window) = app.get_webview_window("main") {
        let _ = window.unminimize();
        let _ = window.show();
        let _ = window.set_focus();
    }
}

// The only server setting. Override API_BASE_URL before launching for HTTPS/local testing.
const DEFAULT_API_BASE_URL: &str = "http://35.185.197.15";
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
        .plugin(tauri_plugin_updater::Builder::new().build())
        .plugin(tauri_plugin_process::init())
        .plugin(tauri_plugin_opener::init())
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
            app.manage(Api::new(base.trim_end_matches('/').into())?);
            let window = app
                .get_webview_window("main")
                .ok_or("Main window missing")?;
            position(&window, false)?;
            let show = MenuItem::with_id(app, "show", "Show widget", true, None::<&str>)?;
            let hide = MenuItem::with_id(app, "hide", "Hide widget", true, None::<&str>)?;
            let quit = MenuItem::with_id(app, "quit", "Quit", true, None::<&str>)?;
            let menu = Menu::with_items(app, &[&show, &hide, &quit])?;
            TrayIconBuilder::with_id("calendar-tray")
                .icon(app.default_window_icon().ok_or("App icon missing")?.clone())
                .tooltip("AAQZ Calendar")
                .menu(&menu)
                .show_menu_on_left_click(false)
                .on_menu_event(|app, event| match event.id.as_ref() {
                    "show" => show_widget(app),
                    "hide" => {
                        if let Some(window) = app.get_webview_window("main") {
                            let _ = window.hide();
                        }
                    }
                    "quit" => app.exit(0),
                    _ => {}
                })
                .on_tray_icon_event(|tray, event| {
                    if let TrayIconEvent::Click {
                        button: MouseButton::Left,
                        button_state: MouseButtonState::Up,
                        ..
                    } = event
                    {
                        show_widget(tray.app_handle());
                    }
                })
                .build(app)?;
            window.show()?;
            Ok(())
        })
        .invoke_handler(tauri::generate_handler![
            calendar,
            login,
            resize_widget,
            account_state,
            resume_account,
            switch_account,
            remove_account,
            workspace,
            note,
            open_shortcut
        ])
        .run(tauri::generate_context!())
        .expect("Unable to start AAQZ Calendar");
}
