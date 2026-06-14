import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { firstValueFrom } from 'rxjs';
import { ApiService } from '../shared/api.service';
import { LoginRequest } from '../shared/models';

@Component({
  selector: 'app-login-page',
  standalone: true,
  imports: [CommonModule, FormsModule],
  template: `
    <section class="panel" style="max-width: 520px; margin: 0 auto;">
      <p class="eyebrow">Admin</p>
      <h1>Sign in</h1>

      <form (ngSubmit)="login()" class="grid-form">
        <label>
          Email
          <input type="email" name="email" [(ngModel)]="model.email" required>
        </label>
        <label>
          Password
          <input type="password" name="password" [(ngModel)]="model.password" required>
        </label>
        <div class="button-row">
          <button class="btn primary" type="submit">Login</button>
        </div>
      </form>

      <p *ngIf="message" class="status">{{ message }}</p>
    </section>
  `
})
export class LoginPageComponent {
  model: LoginRequest = { email: 'admin@example.com', password: '' };
  message = '';

  constructor(
    private readonly api: ApiService,
    private readonly router: Router,
    private readonly route: ActivatedRoute
  ) {}

  async login(): Promise<void> {
    try {
      const auth = await firstValueFrom(this.api.login(this.model));
      this.message = `Welcome, ${auth.fullName}.`;
      const returnUrl = this.route.snapshot.queryParamMap.get('returnUrl') || '/admin/dashboard';
      await this.router.navigateByUrl(returnUrl.startsWith('/') ? returnUrl : `/${returnUrl}`);
    } catch (error) {
      this.message = error instanceof Error ? error.message : 'Login failed.';
    }
  }
}
