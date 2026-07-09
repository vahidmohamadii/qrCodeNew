import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { Router, RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { AuthService } from './shared/auth.service';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive, RouterOutlet],
  template: `
    <div class="app-shell">
      <header class="site-header">
        <a class="brand-link" routerLink="/">Namelenam</a>
        <nav class="nav nav-primary">
          <a routerLink="/" routerLinkActive="active" [routerLinkActiveOptions]="{ exact: true }">Products</a>
          <a routerLink="/about" routerLinkActive="active">About</a>
        </nav>
        <nav class="nav nav-utility">
          <a *ngIf="auth.isAdmin()" routerLink="/admin" routerLinkActive="active">Admin</a>
          <a *ngIf="!auth.isAuthenticated()" routerLink="/admin/login" routerLinkActive="active">Login</a>
          <button *ngIf="auth.isAuthenticated()" type="button" (click)="logout()">Logout</button>
        </nav>
      </header>
      <main>
        <router-outlet></router-outlet>
      </main>
    </div>
  `
})
export class AppComponent {
  constructor(public readonly auth: AuthService, private readonly router: Router) {}

  logout(): void {
    this.auth.logout();
    void this.router.navigate(['/']);
  }
}
