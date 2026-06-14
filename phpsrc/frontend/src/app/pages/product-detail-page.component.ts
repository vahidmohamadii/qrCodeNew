import { CommonModule, CurrencyPipe } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { firstValueFrom } from 'rxjs';
import { ApiService } from '../shared/api.service';
import { PublicProductDto } from '../shared/models';

@Component({
  selector: 'app-product-detail-page',
  standalone: true,
  imports: [CommonModule, CurrencyPipe],
  template: `
    <section class="panel" *ngIf="product; else loading">
      <div class="detail-layout">
        <div class="detail-gallery">
          <img *ngFor="let image of product.images" [src]="api.imageUrl(image.imageUrl)" [alt]="image.altText || product.name">
          <img *ngIf="product.images.length === 0" [src]="api.imageUrl(null)" [alt]="product.name">
        </div>

        <div>
          <p class="eyebrow">{{ product.categoryName }}</p>
          <h1>{{ product.name }}</h1>
          <p class="lead">{{ product.shortDescription }}</p>

          <dl class="detail-list">
            <div><dt>SKU</dt><dd>{{ product.sku }}</dd></div>
            <div><dt>Product Code</dt><dd>{{ product.productCode }}</dd></div>
            <div><dt>Brand</dt><dd>{{ product.brand }}</dd></div>
            <div><dt>Model</dt><dd>{{ product.model }}</dd></div>
            <div><dt>Price</dt><dd>{{ product.price == null ? 'Contact us' : (product.price | currency:(product.currency || 'USD')) }}</dd></div>
          </dl>
        </div>
      </div>

      <div class="detail-grid" style="margin-top: 1rem;">
        <section>
          <h2>Description</h2>
          <p>{{ product.fullDescription }}</p>
        </section>
        <section>
          <h2>Technical Specifications</h2>
          <p>{{ product.technicalSpecifications || '-' }}</p>
        </section>
        <section>
          <h2>Additional Information</h2>
          <p>{{ product.additionalInformation || '-' }}</p>
        </section>
        <section>
          <h2>Warranty</h2>
          <p>{{ product.warrantyInfo || '-' }}</p>
        </section>
      </div>
    </section>

    <ng-template #loading>
      <section class="panel">Loading product...</section>
    </ng-template>
  `
})
export class ProductDetailPageComponent implements OnInit {
  product: PublicProductDto | null = null;

  constructor(public readonly api: ApiService, private readonly route: ActivatedRoute) {}

  async ngOnInit(): Promise<void> {
    const slug = this.route.snapshot.paramMap.get('slug') ?? '';
    this.product = await firstValueFrom(this.api.getProductBySlug(slug));
  }
}
