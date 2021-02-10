export interface IApplicationLocation {
    latitude: string;
    longitude: string
}

export interface IApplication {
    language?: string;
    languageDirection?: string;
    location?: IApplicationLocation
}
