export { LocalStorageAdapter } from './local-storage';

export interface IPersistentStorageAdapter {
    getValue: (name: string) => any;
    setValue: (name: string, value: any) => void;
    removeValue: (name: string) => void;
    removeAllValues: () => void;
}
