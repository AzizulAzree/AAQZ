import { check, type Update, type DownloadEvent } from '@tauri-apps/plugin-updater';
import { relaunch } from '@tauri-apps/plugin-process';

export type AppUpdate = Update;
export const checkForUpdate = () => check({ timeout: 15000 });
export async function installUpdate(update: AppUpdate, progress: (event: DownloadEvent) => void) {
  await update.downloadAndInstall(progress);
  // Windows normally exits into its installer before returning here.
  await relaunch();
}
