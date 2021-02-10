
// services
import { PersistentStorageService } from 'services/persistent-storage';

// fakes
import { PersistentStorageMemoryAdapterFake } from 'test/fake';

describe('PersistentStorage service', () => {

    // testable service
    let persistentStorage: PersistentStorageService;

    beforeEach(() => {
        persistentStorage = new PersistentStorageService( new PersistentStorageMemoryAdapterFake);
    });

    it('getValue should return null if a storage does not contain the requested value', () => {
        expect(persistentStorage.getValue('notExistingValue')).toBeNull();
    });

    it('getValue should return a default value if a storage does not contain the requested value', () => {
        const defaultValue: string = 'defaultValue';

        expect(persistentStorage.getValue('notExistingValue', defaultValue)).toEqual(defaultValue);
    });

    it('getValue should return correct entities like arrays, objects, strings, numbers', () => {
        const stringValue: string = 'defaultValue';

        // strings
        persistentStorage.setValue('stringValue', stringValue);
        expect(persistentStorage.getValue('stringValue')).toEqual(stringValue);

        // numbers
        const numberValue: number = 10;
        persistentStorage.setValue('numberValue', numberValue);
        expect(persistentStorage.getValue('numberValue')).toEqual(numberValue);

        // arrays
        const arrayValue  = [1, '1', false];
        persistentStorage.setValue('arrayValue', arrayValue);
        expect(persistentStorage.getValue('arrayValue')).toEqual(arrayValue);

        const objectValue = {
            a: '1',
            b: [1, 2, 3]
        };
        persistentStorage.setValue('objectValue', objectValue);
        expect(persistentStorage.getValue('objectValue')).toEqual(objectValue);
    });

    it('removeValue should correctly delete values from the store', () => {
        const defaultValue: string = 'defaultValue';

        persistentStorage.setValue('defaultValue', defaultValue);
        expect(persistentStorage.getValue('defaultValue')).toEqual(defaultValue);


        persistentStorage.removeValue('defaultValue');
        expect(persistentStorage.getValue('defaultValue')).toBeNull(defaultValue);
    });

    it('removeAllValues should correctly delete all values from the store', () => {
        const defaultValue: string = 'defaultValue';
        const defaultValue2: string = 'defaultValue2';

        persistentStorage.setValue(defaultValue, defaultValue);
        persistentStorage.setValue(defaultValue2, defaultValue2);

        expect(persistentStorage.getValue(defaultValue)).toEqual(defaultValue);
        expect(persistentStorage.getValue(defaultValue2)).toEqual(defaultValue2);


        persistentStorage.removeAllValues();

        expect(persistentStorage.getValue(defaultValue)).toBeNull();
        expect(persistentStorage.getValue(defaultValue2)).toBeNull();
    });

});
