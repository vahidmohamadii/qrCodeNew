import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { firstValueFrom } from 'rxjs';
import { ApiService } from '../shared/api.service';
import { AuthService } from '../shared/auth.service';
import { CompanyInfoDto } from '../shared/models';

@Component({
  selector: 'app-admin-dashboard-page',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  template: `
    <section class="panel">
      <p class="eyebrow">Dashboard</p>
      <h1>Admin panel</h1>
      <p class="lead">Manage site content, products, images, and QR labels from one place.</p>

      <div class="button-row">
        <a class="btn primary" routerLink="/admin/products">Products</a>
        <a class="btn secondary" routerLink="/admin/categories">Categories</a>
      </div>

      <div class="info-grid" style="margin-top: 1rem;">
        <div><h2>Signed in as</h2><p>{{ auth.currentUser()?.fullName }}</p></div>
        <div><h2>Status</h2><p>{{ auth.isAuthenticated() ? 'Authenticated' : 'Guest' }}</p></div>
      </div>
    </section>

    <section class="panel admin-site-settings">
      <div>
        <p class="eyebrow">Home page</p>
        <h2>Home image</h2>
        <p class="lead">Choose the image shown at the top of the home page.</p>
      </div>

      <div class="home-image-admin">
        <div class="home-image-preview">
          <img [src]="api.imageUrl(companyInfo.homeHeroImageUrl)" alt="Current home image">
        </div>
        <form class="grid-form" (ngSubmit)="uploadHomeImage()">
          <label class="col-span-2">
            New image
            <input type="file" accept="image/*" (change)="handleHomeImageFile($event)">
          </label>
          <label class="col-span-2">
            Home intro text
            <textarea name="description" [(ngModel)]="companyInfo.description"></textarea>
          </label>
          <div class="button-row col-span-2">
            <button class="btn primary" type="submit" [disabled]="saving || !selectedHomeImage">Upload image</button>
            <button class="btn secondary" type="button" (click)="saveIntroText()" [disabled]="saving">Save text</button>
          </div>
          <p class="status col-span-2" *ngIf="message">{{ message }}</p>
        </form>
      </div>
    </section>
  `
})
export class AdminDashboardPageComponent implements OnInit {
  companyInfo: CompanyInfoDto = {
    companyName: 'Namelenam',
    description: '',
    mission: '',
    vision: '',
    services: '',
    contactInformation: '',
    address: '',
    email: '',
    phoneNumber: '',
    socialMediaLinks: null,
    homeHeroImageUrl: '/uploads/site/home-hero-default.png'
  };
  selectedHomeImage?: File;
  saving = false;
  message = '';

  constructor(public readonly auth: AuthService, public readonly api: ApiService) {}

  async ngOnInit(): Promise<void> {
    this.companyInfo = await firstValueFrom(this.api.getCompanyInfo());
  }

  handleHomeImageFile(event: Event): void {
    const input = event.target as HTMLInputElement;
    this.selectedHomeImage = input.files?.[0];
  }

  async uploadHomeImage(): Promise<void> {
    if (!this.selectedHomeImage) {
      this.message = 'Choose an image first.';
      return;
    }

    this.saving = true;
    this.message = '';
    try {
      this.companyInfo = await firstValueFrom(this.api.uploadHomeHeroImage(this.selectedHomeImage));
      this.selectedHomeImage = undefined;
      this.message = 'Home image updated.';
    } catch (error) {
      this.message = error instanceof Error ? error.message : 'Could not upload the image.';
    } finally {
      this.saving = false;
    }
  }

  async saveIntroText(): Promise<void> {
    this.saving = true;
    this.message = '';
    try {
      this.companyInfo = await firstValueFrom(this.api.updateCompanyInfo(this.companyInfo));
      this.message = 'Home text updated.';
    } catch (error) {
      this.message = error instanceof Error ? error.message : 'Could not save the text.';
    } finally {
      this.saving = false;
    }
  }
}
