import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { firstValueFrom } from 'rxjs';
import { ApiService } from '../shared/api.service';
import { ProductImageDto, PublicProductDto } from '../shared/models';

@Component({
  selector: 'app-product-detail-page',
  standalone: true,
  imports: [CommonModule],
  template: `
    <section class="panel" *ngIf="product; else loading">
      <div class="detail-layout">
        <div class="detail-gallery">
          <div class="gallery-main">
            <img class="gallery-main-image" [src]="mainImageUrl()" [alt]="selectedImage?.altText || product.name">
          </div>

          <div class="gallery-thumbs" *ngIf="product.images.length > 1">
            <button
              class="gallery-thumb"
              type="button"
              *ngFor="let image of productImages()"
              [class.active]="image.id === selectedImage?.id"
              (click)="selectImage(image)"
              [attr.aria-label]="image.altText || product.name"
            >
              <img [src]="api.imageUrl(image.imageUrl)" [alt]="image.altText || product.name">
            </button>
          </div>
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
  selectedImage: ProductImageDto | null = null;

  constructor(public readonly api: ApiService, private readonly route: ActivatedRoute) {}

  async ngOnInit(): Promise<void> {
    const slug = this.route.snapshot.paramMap.get('slug') ?? '';
    this.product = await firstValueFrom(this.api.getProductBySlug(slug));
    this.selectedImage = this.productImages()[0] ?? null;
  }

  productImages(): ProductImageDto[] {
    return [...(this.product?.images ?? [])].sort((a, b) => {
      if (a.isMainImage !== b.isMainImage) {
        return a.isMainImage ? -1 : 1;
      }

      return a.sortOrder - b.sortOrder || a.id - b.id;
    });
  }

  selectImage(image: ProductImageDto): void {
    this.selectedImage = image;
  }

  mainImageUrl(): string {
    return this.api.imageUrl(this.selectedImage?.imageUrl);
  }
}
