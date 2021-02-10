import { IPersistentStorageAdapter } from 'services/persistent-storage/adapters';

// mock for storage service
export class PersistentStorageMemoryAdapterFake implements IPersistentStorageAdapter {
    protected storage = {};

    /**
     * Get value
     */
    getValue(name: string): any {

        let value: any = typeof this.storage[name] != 'undefined' ? this.storage[name] : null;

        if (value) {
            return value;
        }

        return null;
    }

    /**
     * Set value
     */
    setValue(name: string, value: any): void {
        this.storage[name] = value;
    }

    /**
     * Remove value
     */
    removeValue (name: string): void {
        if (typeof this.storage[name] != 'undefined') {
            delete this.storage[name];
        }
    }

    /**
     * Remove all values
     */
    removeAllValues(): void {
        this.storage = {};
    };
}
