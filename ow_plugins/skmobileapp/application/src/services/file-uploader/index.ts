import { Injectable } from '@angular/core';
import { ReplaySubject } from 'rxjs/ReplaySubject';
import { Observable } from 'rxjs/Observable';

// services
import { SecureHttpService } from 'services/http';

export interface IFileUploadResult {
    type: number | boolean;
    data?: any;
    extraData?: any;
}

export interface IFileUploadOptions {
    uri?: string;
    fileName?: string;
    allowedMimeTypes?: Array<string>;
    maxFileSize?: number;
    extraData?: any;
    isBroadcastError?: boolean;
}

@Injectable()
export class FileUploaderService {
    public static readonly MIME_TYPES_ERROR_RESULT: number = 0;
    public static readonly FILE_SIZE_ERROR_RESULT: number = 1;
    public static readonly UPLOAD_ERROR_RESULT: number = 2;
    public static readonly UPLOAD_STARTED_RESULT: number = 3;
    public static readonly UPLOAD_PROGRESS_RESULT: number = 4;
    public static readonly SUCCESS_RESULT: number = 5;

    /**
     * Constructor
     */
    constructor(private http: SecureHttpService) {}

    /**
     * Is file valid
     */
    isFileValid(file: File|Blob, options: IFileUploadOptions): number | boolean {
        // validate file mime type
        if (options.allowedMimeTypes 
                && options.allowedMimeTypes.length && options.allowedMimeTypes.indexOf(file.type) === -1) {

            return FileUploaderService.MIME_TYPES_ERROR_RESULT;
        }

        // validate file size
        if (options.maxFileSize && file.size > options.maxFileSize) {
            return FileUploaderService.FILE_SIZE_ERROR_RESULT;
        }

        return true;
    }
 
    /**
     * Upload
     */
    upload(file: File|Blob,  options: IFileUploadOptions, params = {}): Observable<IFileUploadResult> {
        const uploadResult$: ReplaySubject<IFileUploadResult> = new ReplaySubject(1);
        const validationResult: number | boolean = this.isFileValid(file, options);

        // validate the file
        if (validationResult !== true) {
            uploadResult$.next({
                type: validationResult,
                data: null,
                extraData: options.extraData
            });

            uploadResult$.complete();

            return uploadResult$;
        }

        uploadResult$.next({
            type: FileUploaderService.UPLOAD_STARTED_RESULT,
            data: null,
            extraData: options.extraData
        });

        // upload data
        const formData = new FormData();
        formData.append(options.fileName, file);

        this.http.post(options.uri, formData, params, options.isBroadcastError, (percentage: number) => {
            uploadResult$.next({
                type: FileUploaderService.UPLOAD_PROGRESS_RESULT,
                data: percentage,
                extraData: options.extraData
            });
        }).subscribe(data => {
            uploadResult$.next({
                type: FileUploaderService.SUCCESS_RESULT,
                data: data,
                extraData: options.extraData
            });

            uploadResult$.complete();
        }, (error) => {
            uploadResult$.next({
                type: FileUploaderService.UPLOAD_ERROR_RESULT,
                data: error,
                extraData: options.extraData
            });

            uploadResult$.complete();
        });

        return uploadResult$;
    }
}
