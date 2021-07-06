/**
 * Module: TYPO3/CMS/Warming/Backend/ContextMenu/CacheWarmupContextMenuAction
 */
import WarmupRequestMode from '../../../lib/Enums/WarmupRequestMode';

// Modules
import CacheWarmupMenu from '../Toolbar/CacheWarmupMenu';

class CacheWarmupContextMenuAction {
  public static warmupPageCache(table: string, uid: number): void {
    if ('pages' === table) {
      CacheWarmupMenu.warmupCache(uid, WarmupRequestMode.Page);
    }
  }

  public static warmupSiteCache(table: string, uid: number): void {
    if ('pages' === table) {
      CacheWarmupMenu.warmupCache(uid, WarmupRequestMode.Site);
    }
  }
}

export default new CacheWarmupContextMenuAction();

// We need to export the static methods separately to ensure those functions
// can be properly triggered by ContextMenu.ts from sysext EXT:backend, see
// https://github.com/TYPO3/TYPO3.CMS/blob/bb831f2272815cae672dd382161f0bb9e6123b8e/Build/Sources/TypeScript/backend/Resources/Public/TypeScript/ContextMenu.ts#L200
export const warmupPageCache = CacheWarmupContextMenuAction.warmupPageCache;
export const warmupSiteCache = CacheWarmupContextMenuAction.warmupSiteCache;
