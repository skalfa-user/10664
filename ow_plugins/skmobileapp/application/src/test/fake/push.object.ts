import { EventResponse, PushEvent } from '@ionic-native/push';
import { Observable } from 'rxjs/Observable';

export class PushObjectFake {
    on(event: PushEvent): Observable<EventResponse> {
        return Observable.empty();
    }

    unregister(): Promise<any> {
        return Promise.resolve('');
    }

    setApplicationIconBadgeNumber(count?: number): Promise<any> {
        return Promise.resolve('');
    }

    getApplicationIconBadgeNumber(): Promise<number> {
        return Promise.resolve(1);
    }

    finish(id?: string): Promise<any> {
        return Promise.resolve('');
    }

    clearAllNotifications(): Promise<any> {
        return Promise.resolve('');
    }

    subscribe(topic: string): Promise<any> {
        return Promise.resolve('');
    }

    unsubscribe(topic: string): Promise<any> {
        return Promise.resolve('');
    }
}
