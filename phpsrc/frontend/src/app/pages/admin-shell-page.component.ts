import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { AuthService } from '../shared/auth.service';

@Component({
  selector: 'app-admin-shell-page',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive, RouterOutlet],
  template: `
    <section class="admin-shell">
      <div class="admin-shell-head">
        <div>
          <p class="eyebrow">Admin</p>
          <h1>Admin panel</h1>
          <p class="lead">Switch between home content, products, and categories without leaving the panel.</p>
        </div>
        <div class="admin-shell-meta">
          <div><span class="muted">Signed in as</span><strong>{{ auth.currentUser()?.fullName }}</strong></div>
          <div><span class="muted">Status</span><strong>{{ auth.isAuthenticated() ? 'Authenticated' : 'Guest' }}</strong></div>
        </div>
      </div>

      <nav class="admin-tabs" aria-label="Admin sections">
        <a routerLink="/admin/home" routerLinkActive="active">Home page</a>
        <a routerLink="/admin/products" routerLinkActive="active">Add product</a>
        <a routerLink="/admin/categories" routerLinkActive="active">Product categories</a>
      </nav>

      <router-outlet></router-outlet>
    </section>
  `
})
export class AdminShellPageComponent {
  constructor(public readonly auth: AuthService) {}
}
