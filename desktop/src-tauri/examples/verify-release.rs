// Verify publishing artifacts against the public key shipped in the widget.
// This never loads the private signing key and never runs the installer.
use base64::{engine::general_purpose::STANDARD, Engine};
use minisign_verify::{PublicKey, Signature};
use std::{env, fs};

fn main() -> Result<(), Box<dyn std::error::Error>> {
    let installer = env::args().nth(1).ok_or("Pass the installer path")?;
    let config: serde_json::Value = serde_json::from_str(include_str!("../tauri.conf.json"))?;
    let key_text = String::from_utf8(
        STANDARD.decode(
            config["plugins"]["updater"]["pubkey"]
                .as_str()
                .ok_or("Public key missing")?,
        )?,
    )?;
    let signature_text = String::from_utf8(
        STANDARD.decode(fs::read_to_string(format!("{installer}.sig"))?.trim())?,
    )?;
    let public_key = PublicKey::decode(&key_text)?;
    let signature = Signature::decode(&signature_text)?;
    let mut bytes = fs::read(installer)?;
    public_key.verify(&bytes, &signature, true)?;
    bytes[0] ^= 1;
    if public_key.verify(&bytes, &signature, true).is_ok() {
        return Err("Modified installer was incorrectly accepted".into());
    }
    println!("Installer signature verified; modified installer rejected.");
    Ok(())
}
