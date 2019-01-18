import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { User } from '@app/_models';
import { AppSettings } from '@app/app.settings';

@Injectable({ providedIn: 'root' })
export class UserService {
    constructor(private http: HttpClient) { }

    register(user: User) {
        var request = {
            variables: {
                  user: user
            },
            query: "mutation($user:CreateUser){register(user: $user){username,firstName,lastName}}"
        }
        return this.http.post(AppSettings.API_ENDPOINT, JSON.stringify(request));
    }
}