import { Routes, RouterModule } from '@angular/router';

import { HomeComponent } from '@app/home';
import { LoginComponent } from '@app/login';
import { RegisterComponent } from '@app/register';
import { AuthGuard } from '@app/_guards';

const appRoutes: Routes = [
    { path: '', component: HomeComponent, canActivate: [AuthGuard] },
    { path: 'login', component: LoginComponent },
    { path: 'register', component: RegisterComponent },

    // otherwise redirect to home
    { path: '**', redirectTo: '' }
];

export const routing = RouterModule.forRoot(appRoutes);