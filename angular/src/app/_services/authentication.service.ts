import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';

import { User } from '@app/_models';
import { AppSettings } from '@app/app.settings';
import { AlertService } from '@app/_services';

@Injectable({ providedIn: 'root' })
export class AuthenticationService {
    private currentUserSubject: BehaviorSubject<User>;
    public currentUser: Observable<User>;

    constructor(private http: HttpClient, private alertService: AlertService) {
        this.currentUserSubject = new BehaviorSubject<User>(JSON.parse(localStorage.getItem('currentUser')));
        this.currentUser = this.currentUserSubject.asObservable();
    }

    public get currentUserValue(): User {
        return this.currentUserSubject.value;
    }

    login(username: string, password: string) {
        let request = {
            variables: {
                  name: username,
                  pass: password
            },
            query: "query($name:String, $pass:String){authentificate(username: $name, password: $pass){username,firstName,lastName,token}}"
        }
        return this.http.post<any>(AppSettings.API_ENDPOINT, JSON.stringify(request))
            .pipe(map(responce => {
                let user = responce.data.authentificate;
                // login successful if there's a jwt token in the response
                if (user && user.token) {
                    // store user details and jwt token in local storage to keep user logged in between page refreshes
                    localStorage.setItem('currentUser', JSON.stringify(user));
                    this.currentUserSubject.next(user);
                }

                return user;
            }));
    }

    checkAuth() {
        this.http.post<any>(AppSettings.CHECK_AUTH, {})
            .subscribe(
                data => { },
                error => {
                    this.alertService.error(error, true);
                }
            );
    }

    logout() {
        // remove user from local storage to log user out
        localStorage.removeItem('currentUser');
        this.currentUserSubject.next(null);
    }
}