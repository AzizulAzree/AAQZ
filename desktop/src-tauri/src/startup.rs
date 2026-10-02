// The autostart dependency writes unquoted Windows paths. Correct our entry only.
#[cfg(target_os = "windows")]
pub fn repair() -> Result<(), Box<dyn std::error::Error>> {
    use winreg::{enums::{HKEY_CURRENT_USER, KEY_READ, KEY_SET_VALUE}, RegKey};
    let key = RegKey::predef(HKEY_CURRENT_USER)
        .open_subkey_with_flags("Software\\Microsoft\\Windows\\CurrentVersion\\Run", KEY_READ | KEY_SET_VALUE)?;
    if let Ok(current) = key.get_value::<String, _>("AAQZ Calendar") {
        let executable = std::env::current_exe()?;
        let command = format!("\"{}\"", executable.display());
        if current != command { key.set_value("AAQZ Calendar", &command)?; }
    }
    Ok(())
}
#[cfg(not(target_os = "windows"))]
pub fn repair() -> Result<(), Box<dyn std::error::Error>> { Ok(()) }
