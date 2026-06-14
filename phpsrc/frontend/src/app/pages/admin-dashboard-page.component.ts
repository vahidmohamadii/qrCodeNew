import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';
import { AuthService } from '../shared/auth.service';

@Component({
  selector: 'app-admin-dashboard-page',
  standalone: true,
  imports: [RouterLink],
  template: `
    <section class="panel">
      <p class="eyebrow">Dashboard</p>
      <h1>Admin panel</h1>
      <p class="lead">Manage categories, products, images, and QR labels from one place.</p>

      <div class="button-row">
        <a class="btn primary" routerLink="/admin/products">Products</a>
        <a class="btn secondary" routerLink="/admin/categories">Categories</a>
      </div>

      <div class="info-grid" style="margin-top: 1rem;">
        <div><h2>Signed in as</h2><p>{{ auth.currentUser()?.fullName }}</p></div>
        <div><h2>Status</h2><p>{{ auth.isAuthenticated() ? 'Authenticated' : 'Guest' }}</p></div>
      </div>
    </section>
  `
})
export class AdminDashboardPageComponent {
  constructor(public readonly auth: AuthService) {}
}
