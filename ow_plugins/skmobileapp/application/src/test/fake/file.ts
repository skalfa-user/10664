export function createFakeFile(name: string = 'mock.txt', size: number = 100, mimeType: string = 'plain/txt') {
    const blob = new Blob([range(size)], { type: mimeType });

    blob['lastModifiedDate'] = new Date();
    blob['name'] = name;

    return blob;
};

function range(size: number): string {
    let output: string = '';

    for (let i = 0; i < size; i++) {
        output += 'a';
    }

    return output;
}
