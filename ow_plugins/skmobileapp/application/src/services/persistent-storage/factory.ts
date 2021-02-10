import { PersistentStorageService } from './';
import { LocalStorageAdapter } from './adapters';

/**
 * Persistent storage factory
 */
export function persistentStorageFactory (): PersistentStorageService {
    return new PersistentStorageService( new LocalStorageAdapter);
}
