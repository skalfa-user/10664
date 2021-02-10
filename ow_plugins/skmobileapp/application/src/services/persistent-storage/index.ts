// services
import { IPersistentStorageAdapter } from './adapters';

export class PersistentStorageService
{
    /**
     *  Constructor
     */
    constructor (private storageAdapter: IPersistentStorageAdapter) {}

    /**
     * Get value
     */
    getValue(name: string, defaultValue: any = null): any {
        let storageValue: string = this.storageAdapter.getValue(name);
        let value: any = storageValue ? JSON.parse(storageValue) : null;

        if (value === null && defaultValue !== null) {
            return defaultValue;
        }

        return value;
    }

    /**
     * Set value
     */
    setValue(name: string, value: any): void {
        this.storageAdapter.setValue(name, JSON.stringify(value));
    }

    /**
     * Remove value
     */
    removeValue(name: string): void {
        this.storageAdapter.removeValue(name);
    }

    /**
     * Remove all values
     */
    removeAllValues(): void {
        this.storageAdapter.removeAllValues();
    }
}
