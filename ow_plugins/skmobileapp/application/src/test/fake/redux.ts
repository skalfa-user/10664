import { NgRedux } from '@angular-redux/store';

export class ReduxFake extends NgRedux<any> {
    dispatch = () => undefined;
    getState = () => undefined;
    configureSubStore = () => undefined;
    subscribe = () => undefined;
    select = () => undefined;
    replaceReducer = () => undefined;
    provideStore = () => undefined;
    configureStore = () => undefined;
}
