import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

import { Session } from '@app/_models'
import { AlertService, AuthenticationService, WebsocketService } from '@app/_services';


@Component({ templateUrl: 'home.component.html' })
export class HomeComponent implements OnInit {
    createSessionForm: FormGroup;
    loading = false;
    submitted = false;

    sessions: Session[];

    constructor(
        private formBuilder: FormBuilder,
        private authenticationService: AuthenticationService,
        private alertService: AlertService,
        private websocketService: WebsocketService
    ) {
        this.authenticationService.checkAuth();
    }

    get f() { return this.createSessionForm.controls; }

    ngOnInit() {
        this.createSessionForm = this.formBuilder.group({
            name: ['', Validators.required],
            password: ['', [Validators.required, Validators.minLength(6)]]
        });

        console.dir(this.websocketService.websocket);
        //this.websocketService.message(1);
            /*.subscribe(
                message => {
                    console.dir(message);
            });*/

        /*this.websocketService.send({
            'type': 'sessionMessage', 
            'action' : 'open',
            'broad' : true,
            'sessionID' : 10 
        });*/
        
        /*this.sessions = [];
        let session = {
            id: 1,
            name: "lol",
            formBuilder: this.formBuilder.group({
                name: ['', Validators.required],
                password: ['', [Validators.required, Validators.minLength(6)]]
            })
        };
        this.sessions.push(session);*/
    }

    onSubmit() {
        //TODO
        this.submitted = true;

        // stop here if form is invalid
        if (this.createSessionForm.invalid) {
            return;
        }

        this.submitted = false;
    }
}