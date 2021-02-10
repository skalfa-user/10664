import { IPersistentStorageAdapter } from 'services/persistent-storage/adapters';

export class LocalStorageAdapter implements IPersistentStorageAdapter {
    /**
     * Get value
     */
    getValue(name: string):any {
        let value: any = localStorage.getItem(name);

        if (value) {
            return value;
        }

        return null;
    }

    /**
     * Set value
     */
    setValue(name: string, value: any): void {
        localStorage.setItem(name, value);
    }

    /**
     * Remove value
     */
    removeValue (name: string): void {
        localStorage.removeItem(name);
    }

    /**
     * Remove all values
     */
    removeAllValues(): void {
        localStorage.clear();
    };
}
