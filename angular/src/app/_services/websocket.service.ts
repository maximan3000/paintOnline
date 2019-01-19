import { Injectable } from '@angular/core';
import { Observable, fromEvent } from 'rxjs';

import { AppSettings } from '@app/app.settings'

@Injectable({ providedIn: 'root' })
export class WebsocketService {
	private websocket: WebSocket;
	
	constructor() { 
		this.websocket = new WebSocket(AppSettings.SOCKET_URL);
	}

	/* public send(message: any): void {
        this.websocket.send(JSON.stringify(message));
    }*/

    public message(i: number): void {
    	alert(i);
        return ;
    }

       /* new Observable<any>(observer => {
            this.websocket.onmessage = (data: any) => observer.next(JSON.parse(data.data));
        });*/
    

    /*public onOpen(): Observable<any> {
        return new Observable<any>(observer => {
            this.websocket.onopen = (data: any) => observer.next(JSON.parse(data.data));
        });
    }

    public onClose(): Observable<any> {
        return new Observable<any>(observer => {
            this.websocket.onclose = (data: any) => observer.next(JSON.parse(data.data));
        });
    }

    public onError(): Observable<any> {
        return new Observable<any>(observer => {
            this.websocket.onerror = (data: any) => observer.next(JSON.parse(data.data));
        });
    }*/
}