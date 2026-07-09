import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { firstValueFrom } from 'rxjs';
import { ApiService } from '../shared/api.service';
import { CategoryDto, CompanyInfoDto, ProductDto } from '../shared/models';

@Component({
  selector: 'app-products-page',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  template: `
    <section class="home-hero">
      <div class="home-hero-copy">
        <p class="eyebrow">{{ companyInfo?.companyName || 'Namelenam' }}</p>
        <h1>Products</h1>
        <p class="lead">{{ companyInfo?.description || 'Browse products and open public product pages from QR labels.' }}</p>
      </div>
      <div class="home-hero-media">
        <img [src]="api.imageUrl(companyInfo?.homeHeroImageUrl)" alt="Namelenam product catalog and QR labels">
      </div>
    </section>

    <section class="panel">
      <div class="filters">
        <label>
          Search
          <input [(ngModel)]="search" (keyup.enter)="loadProducts()" placeholder="Name, SKU, or product code">
        </label>
        <label>
          Category
          <select [(ngModel)]="selectedCategoryId">
            <option [ngValue]="null">All categories</option>
            <option *ngFor="let category of categories" [ngValue]="category.id">{{ category.name }}</option>
          </select>
        </label>
        <button class="btn primary" type="button" (click)="loadProducts()">Search</button>
      </div>

      <div class="product-grid">
        <a class="product-card" *ngFor="let product of products" [routerLink]="['/products', product.slug]">
          <img [src]="productImage(product)" [alt]="product.name">
          <div class="product-card-body">
            <span class="muted">{{ product.categoryName }}</span>
            <h2>{{ product.name }}</h2>
            <p>{{ product.shortDescription }}</p>
            <div class="meta-row">
              <span>{{ product.sku }}</span>
            </div>
          </div>
        </a>
      </div>
    </section>
  `
})
export class ProductsPageComponent implements OnInit {
  products: ProductDto[] = [];
  categories: CategoryDto[] = [];
  companyInfo?: CompanyInfoDto;
  search = '';
  selectedCategoryId: number | null = null;

  constructor(public readonly api: ApiService) {}

  async ngOnInit(): Promise<void> {
    const [companyInfo, categories] = await Promise.all([
      firstValueFrom(this.api.getCompanyInfo()),
      firstValueFrom(this.api.getCategories())
    ]);
    this.companyInfo = companyInfo;
    this.categories = categories;
    await this.loadProducts();
  }

  async loadProducts(): Promise<void> {
    this.products = await firstValueFrom(this.api.getProducts(this.search, this.selectedCategoryId));
  }

  productImage(product: ProductDto): string {
    const image = [...product.images].sort((a, b) => {
      if (a.isMainImage !== b.isMainImage) {
        return a.isMainImage ? -1 : 1;
      }

      return a.sortOrder - b.sortOrder || a.id - b.id;
    })[0];
    return this.api.imageUrl(image?.imageUrl);
  }
}
