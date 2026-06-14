import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { firstValueFrom } from 'rxjs';
import { ApiService } from '../shared/api.service';
import { CompanyInfoDto } from '../shared/models';

@Component({
  selector: 'app-about-page',
  standalone: true,
  imports: [CommonModule],
  template: `
    <section class="panel" *ngIf="company; else loading">
      <p class="eyebrow">About</p>
      <h1>{{ company.companyName }}</h1>
      <p class="lead">{{ company.description }}</p>

      <div class="info-grid">
        <div><h2>Mission</h2><p>{{ company.mission }}</p></div>
        <div><h2>Vision</h2><p>{{ company.vision }}</p></div>
        <div><h2>Services</h2><p>{{ company.services }}</p></div>
        <div><h2>Contact</h2><p>{{ company.contactInformation }}</p></div>
      </div>

      <dl class="detail-list" style="margin-top: 1rem;">
        <div><dt>Address</dt><dd>{{ company.address }}</dd></div>
        <div><dt>Email</dt><dd>{{ company.email }}</dd></div>
        <div><dt>Phone</dt><dd>{{ company.phoneNumber }}</dd></div>
      </dl>
    </section>

    <ng-template #loading>
      <section class="panel">Loading company profile...</section>
    </ng-template>
  `
})
export class AboutPageComponent implements OnInit {
  company: CompanyInfoDto | null = null;

  constructor(private readonly api: ApiService) {}

  async ngOnInit(): Promise<void> {
    this.company = await firstValueFrom(this.api.getCompanyInfo());
  }
}
