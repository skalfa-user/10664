import { Component, ChangeDetectionStrategy, Input, Output, ViewChild, ElementRef, EventEmitter } from '@angular/core';
import { AlertController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';

// services
import { FileUploaderService, IFileUploadOptions } from 'services/file-uploader';

export interface IFileUploadResult {
    data: any;
    extraData: any;
}

@Component({
    selector: 'file-uploader',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class FileUploaderComponent {
    @ViewChild('file') fileInput: ElementRef;

    @Input() uri: string = null;
    @Input() fileName: string = 'file';
    @Input() maxFileSize: number = 0;
    @Input() acceptMask: string = ''; // video/* | image/*, etc (optional)
    @Input() mimeTypes: Array<string> = []; // video/mp4 | image/jpg | image/png, etc (optional)
    @Input() extraData: any = null;

    @Input() set isValidateAndReturn (value: any) {
        this.isOnlyValidateAndReturn = value === 'true' ||  value === true;
    }
    @Input() set isBroadcastError (value: any) {
        this.isBroadcastErrorFurther = value === 'true' ||  value === true;
    }

    @Output() fileSelected = new EventEmitter<IFileUploadResult>();
    @Output() startUploading = new EventEmitter<IFileUploadResult>();
    @Output() uploadProgress = new EventEmitter<IFileUploadResult>();
    @Output() uploaded = new EventEmitter<IFileUploadResult>();
    @Output() uploadError = new EventEmitter<IFileUploadResult>();

    private isBroadcastErrorFurther: boolean = false;
    private isOnlyValidateAndReturn: boolean = false;

    /**
     * Constructor
     */
    constructor(
        private translate: TranslateService,
        private fileUploader: FileUploaderService, 
        private alert: AlertController) {}

    /**
     * Show file chooser
     */
    showFileChooser(): void {
        this.fileInput.nativeElement.value = '';
        this.fileInput.nativeElement.click();
    }

    public setupUri(uri: string): void {
        this.uri = uri;
    }

    /**
     * Process file
     */
    processFile(): void {
        const inputEl: HTMLInputElement = this.fileInput.nativeElement;

        if (inputEl.files.length) {
            const file: File = inputEl.files.item(0);

            // init file uploader
            const fileUploadOptions: IFileUploadOptions = {
                uri: this.uri,
                fileName: this.fileName,
                allowedMimeTypes: this.mimeTypes,
                maxFileSize: this.maxFileSize,
                extraData: this.extraData,
                isBroadcastError: this.isBroadcastErrorFurther
            };

            // validate the file
            const validationResult: boolean | number = this.fileUploader.isFileValid(file, fileUploadOptions);

            // validation is failed
            if (validationResult !== true) {
                this.processFileValidationError(file, validationResult);

                return;
            }

            this.fileSelected.emit({
                data: file,
                extraData: this.extraData
            });

            console.log(file, fileUploadOptions);
            // upload the file
            if (!this.isOnlyValidateAndReturn) {
                this.upload(file, fileUploadOptions);
            }
        }
    }

    /**
     * Process file validation error
     */
    private processFileValidationError(file: File, errorCode: number | boolean): void {
        switch(errorCode) {
            case FileUploaderService.MIME_TYPES_ERROR_RESULT :
                const mimeAlert = this.alert.create({
                    title: this.translate.instant('error_occurred'),
                    subTitle: this.translate.instant('error_file_mime_type', {
                        mimeTypes: this.mimeTypes.join(', ')
                    }),
                    buttons: [this.translate.instant('ok')]
                });

                mimeAlert.present();
                break;

            case FileUploaderService.FILE_SIZE_ERROR_RESULT :
                const sizeAlert = this.alert.create({
                    title: this.translate.instant('error_occurred'),
                    subTitle: this.translate.instant('error_file_exceeds_max_upload_size', {
                        fileSize:  this.convertBytesToMb(file.size),
                        allowedSize: this.convertBytesToMb(this.maxFileSize),
                    }),
                    buttons: [this.translate.instant('ok')]
                });

                sizeAlert.present();
                break;

            default :
        }
    }

    /**
     * Upload
     */
    private upload(file: File, fileUploadOptions: IFileUploadOptions): void {
        // upload file
        this.fileUploader.upload(file, fileUploadOptions).subscribe(response => {
            switch(response.type) {
                 case FileUploaderService.UPLOAD_STARTED_RESULT :
                    this.startUploading.emit({
                        data: response.data,
                        extraData: response.extraData
                    });
                    break;

                case FileUploaderService.UPLOAD_PROGRESS_RESULT :
                    this.uploadProgress.emit({
                        data: response.data,
                        extraData: response.extraData
                    });
                    break;

                case FileUploaderService.UPLOAD_ERROR_RESULT :
                    this.uploadError.emit({
                        data: response.data,
                        extraData: response.extraData
                    });
                    break;

                case FileUploaderService.SUCCESS_RESULT :
                    this.uploaded.emit({
                        data: response.data,
                        extraData: response.extraData
                    });
                    break;

                default :
            }
        });
    }

    /**
     * Convert bytes to megabytes
     */
    private convertBytesToMb(bytes: number): string {
        const megabytes: number = bytes / 1024 / 1024;

        return megabytes.toFixed(1);
    }
}
