import { Injectable } from '@angular/core';
import { Camera, CameraOptions } from '@ionic-native/camera';
import { TranslateService } from 'ng2-translate';
import { Transfer, TransferObject, FileUploadOptions, FileTransferError } from '@ionic-native/transfer';
import { AlertController, Platform } from 'ionic-angular';
import { File, Entry } from '@ionic-native/file';
import { MediaCapture, CaptureVideoOptions } from '@ionic-native/media-capture';
import { Observable } from 'rxjs/Observable';

// import services
import { AuthService } from '../auth/index';
import { SecureHttpService } from 'services/http';

import {
    IVideoDataResponse,
    IVideoUserDataResponse,
    IVideoPreview } from './responses';

export {
    IVideoDataResponse,
    IVideoPreview,
    IVideoUserDataResponse
} from './responses';

export type VideoUploaderSource = 'camera' | 'library';

@Injectable()
export class VideoUploaderService {
    public maxDuration: number = 30;
    public maxFileSizeMb: number = 30;
    public url: string;
    public httpMethod: string = 'POST';
    public progressCallback: (any);
    public successUploadCallback: (any);
    public errorUploadCallback: (any);
    public startUploadingCallback: (any);
    public abortUploadingCallback: (any);

    private fileTransfer: TransferObject;

    private skippedNativeErrors: (string)[] = [
        'No Image Selected',
        'has no access to assets',
        'Selection cancelled.',
        'Camera cancelled.',
    ];

    // error codes
    public static ERROR_SELECTING_VIDEO: string = 'error_selecting_video';
    public static ERROR_UPLOADING_FILE: string = 'error_uploading_file';
    public static ERROR_GETTING_FILE_INFO: string = 'error_getting_file_info';
    public static ERROR_MAX_SIZE_LIMIT_EXCEEDED: string = 'error_max_size_limit_exceeded';

    /**
     * Constructor
     */
    constructor(
        private mediaCapture: MediaCapture,
        private http: SecureHttpService,
        private file: File,
        private platform: Platform,
        private alert: AlertController,
        private transfer: Transfer,
        private auth: AuthService,
        private camera: Camera,
        private translate: TranslateService) {}

    /**
     * Take video
     */
    async takeVideo(fromSource: VideoUploaderSource): Promise<any> {
        let sourceType: number = fromSource == 'camera'
            ? this.camera.PictureSourceType.CAMERA
            : this.camera.PictureSourceType.PHOTOLIBRARY;

        console.log(fromSource);
        console.log(sourceType);
        // take a video
        if (fromSource == 'camera') {
            // record video
            try {
                let options: CaptureVideoOptions = {
                    duration: this.maxDuration
                };

                let video: any = await this.mediaCapture.captureVideo(options);

                // validate file size
                let fileSize: number = video[0].size / 1024 / 1024;

                // check the file  size
                if (fileSize <= this.maxFileSizeMb) {
                    // upload video
                    this.uploadVideo(video[0].fullPath);
                }
                else {
                    this.errorUploadCallback.call(null, {
                        type: VideoUploaderService.ERROR_MAX_SIZE_LIMIT_EXCEEDED,
                        message: 'The uploaded file exceeds the max upload file size'
                    });

                    this.showAlert('error_file_exceeds_max_upload_size', {
                        fileSize: fileSize.toFixed(1),
                        allowedSize: this.maxFileSizeMb.toFixed(1)
                    });
                }
            }
            catch(e) {
                if (e.code != 3) { // canceled
                    if (this.errorUploadCallback) {
                        this.errorUploadCallback.call(null, {
                            type: VideoUploaderService.ERROR_SELECTING_VIDEO,
                            message: e.message
                        });
                    }

                    this.showAlert('error_selecting_video');

                    throw e;
                }
            }
        }
        else {
            try {
                // choose video from library
                let options: CameraOptions = {
                    sourceType: sourceType,
                    mediaType: this.camera.MediaType.VIDEO,
                    saveToPhotoAlbum: false
                };

                let videoPath: string = await this.camera.getPicture(options);

                console.log(videoPath);

                if (videoPath) {
                    // small bug fix only for android
                    if (this.platform.is('android')) {
                        videoPath = 'file://' + videoPath;
                    }

                    let file:Entry = await this.file.resolveLocalFilesystemUrl(videoPath);

                    // get file meta data
                    file.getMetadata((file: any) => {
                        // validate file size
                        let fileSize: number = file.size / 1024 / 1024;

                        // check the file  size
                        if (fileSize <= this.maxFileSizeMb) {
                            // upload video
                            this.uploadVideo(videoPath);
                        }
                        else {
                            this.errorUploadCallback.call(null, {
                                type: VideoUploaderService.ERROR_MAX_SIZE_LIMIT_EXCEEDED,
                                message: 'The uploaded file exceeds the max upload file size'
                            });

                            this.showAlert('error_file_exceeds_max_upload_size', {
                                fileSize: fileSize.toFixed(1),
                                allowedSize: this.maxFileSizeMb.toFixed(1)
                            });
                        }

                    }, (error) => {
                        if (this.errorUploadCallback) {
                            this.errorUploadCallback.call(null, {
                                type: VideoUploaderService.ERROR_GETTING_FILE_INFO,
                                message: error.code
                            });
                        }

                        this.showAlert('error_getting_file_info');
                    });

                    return;
                }

                // path is empty (at this moment camera plugin cannot get file url from google drive)
                throw new Error('Video file path is empty');
            }
            catch (e) {
                if (this.errorUploadCallback) {
                    this.errorUploadCallback.call(null, {
                        type: VideoUploaderService.ERROR_SELECTING_VIDEO,
                        message: e.message
                    });
                }

                // if (!this.skippedNativeErrors.includes(e)) {
                if (this.skippedNativeErrors.indexOf(e) === -1) {
                    this.showAlert('error_selecting_video');

                    throw e;
                }
            }
        }
    }

    loadUserVideo(): Observable<IVideoDataResponse> {
        return this.http.get('/videos/user-video');
    }

    updateVideoData(data: Array<any>): Observable<any> {
        return this.http.put('/videos', data);
    }

    /**
     * Load autocomplete
     */
    loadAutocomplete(query: string, addedUser: Array<any>): Observable<Array<IVideoUserDataResponse>> {
        return this.http.post('/videos/user-search', {
            q: '@' + query,
            addedUser: addedUser
        });
    }
    /**
     * Abort uploading
     */
    abortUploading(): void {

        if (this.abortUploadingCallback) {
            this.abortUploadingCallback.call(null);
        }
    }

    /**
     * Upload video
     */
    uploadVideo(videoPath: string): void {
        if (this.startUploadingCallback) {
            this.startUploadingCallback.call(null);
        }

        let language = this.translate.currentLang
            ? this.translate.currentLang
            : this.translate.getDefaultLang();

        let headers = {
            'api-language': language
        };

        // add auth header
        if (this.auth.getToken()) {
            headers[this.auth.getAuthHeaderName()] = this.auth.getAuthHeaderValue();
        }
        let options: FileUploadOptions = {
            fileKey: 'file',
            fileName: videoPath,
            chunkedMode: false,
            httpMethod: this.httpMethod,
            params : {
                fileName: videoPath
            },
            headers: headers
        };

        this.fileTransfer = this.transfer.create();

        // use the FileTransfer to upload the image
        this.fileTransfer.upload(videoPath, this.url, options)
            .then((data) => {
                if (this.successUploadCallback) {
                    this.successUploadCallback.call(null, JSON.parse(data.response));
                }
            }, (err: FileTransferError) => {
                if (err.code != 4) { // skip abort uploading errors
                    if (this.errorUploadCallback) {
                        this.errorUploadCallback.call(null, {
                            type: VideoUploaderService.ERROR_UPLOADING_FILE,
                            message: err.body
                        });
                    }

                    this.showAlert('error_uploading_file');
                }
            });

        // on upload progress
        this.fileTransfer.onProgress((result: any) => {
            let percent =  result.loaded / result.total * 100;
            percent = Math.round(percent);

            if (this.progressCallback) {
                this.progressCallback.call(null, percent);
            }
        });
    }

    /**
     * Show alert
     */
    private showAlert(description: string, params?: Object): void {
        let alert = this.alert.create({
            title: this.translate.instant('error_occurred'),
            subTitle: this.translate.instant(description, params),
            buttons: [this.translate.instant('ok')]
        });

        alert.present();
    }
}
