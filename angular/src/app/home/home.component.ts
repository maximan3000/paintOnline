import { Component, OnInit, OnDestroy } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Subscription } from 'rxjs';

import { User } from '@app/_models';
import { AlertService, AuthenticationService } from '@app/_services';


@Component({ templateUrl: 'home.component.html' })
export class HomeComponent implements OnInit, OnDestroy {
    currentUser: User;
    currentUserSubscription: Subscription;

    createSessionForm: FormGroup;
    loading = false;
    submitted = false;

    constructor(
        private formBuilder: FormBuilder,
        private authenticationService: AuthenticationService,
        private alertService: AlertService
    ) {
        this.currentUserSubscription = this.authenticationService.currentUser.subscribe(user => {
            this.currentUser = user;
        });
        this.authenticationService.checkAuth();
    }

    get f() { return this.createSessionForm.controls; }

    ngOnInit() {
        this.createSessionForm = this.formBuilder.group({
            name: ['', Validators.required],
            password: ['', [Validators.required, Validators.minLength(6)]]
        });
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

    ngOnDestroy() {
        // unsubscribe to ensure no memory leaks
        this.currentUserSubscription.unsubscribe();
    }
}