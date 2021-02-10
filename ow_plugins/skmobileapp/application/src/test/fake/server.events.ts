import { ServerEventsService } from 'services/server-events';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/observable/empty' 

// responses
import { IServerEventsResponse } from 'services/server-events/responses';

export class ServerEventsServiceFake extends ServerEventsService { 
    watchData(channelName: string): Observable<IServerEventsResponse> {
        return Observable.empty<IServerEventsResponse>();
    }

    protected restartServerEvents(): void {}
}
