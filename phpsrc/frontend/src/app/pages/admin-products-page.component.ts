import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { firstValueFrom } from 'rxjs';
import { ApiService } from '../shared/api.service';
import {
  CategoryDto,
  ProductDto,
  ProductImageDto,
  ProductQrCodeDto,
  ProductUpsertRequest,
  QrCodeCreateRequest
} from '../shared/models';

@Component({
  selector: 'app-admin-products-page',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  template: `
    <section class="page-head">
      <div>
        <p class="eyebrow">Catalog</p>
        <h2>Products</h2>
        <p class="lead">Create products, upload images, and generate QR labels.</p>
      </div>
    </section>

    <section class="panel">
      <h2>Create or edit product</h2>
      <form (ngSubmit)="save()" class="grid-form">
        <label>
          Category
          <select name="categoryId" [(ngModel)]="editor.categoryId" required>
            <option [ngValue]="0">Choose a category</option>
            <option *ngFor="let category of categories" [ngValue]="category.id">{{ category.name }}</option>
          </select>
        </label>
        <label>
          Name
          <input name="name" [(ngModel)]="editor.name" required>
        </label>
        <label>
          Slug
          <input name="slug" [(ngModel)]="editor.slug">
        </label>
        <label>
          SKU
          <input name="sku" [(ngModel)]="editor.sku" required>
        </label>
        <label>
          Product Code
          <input name="productCode" [(ngModel)]="editor.productCode" required>
        </label>
        <label>
          Brand
          <input name="brand" [(ngModel)]="editor.brand">
        </label>
        <label>
          Model
          <input name="model" [(ngModel)]="editor.model">
        </label>
        <label>
          Stock
          <input type="number" name="stockQuantity" [(ngModel)]="editor.stockQuantity">
        </label>
        <label>
          Featured
          <input type="checkbox" name="isFeatured" [(ngModel)]="editor.isFeatured">
        </label>
        <label>
          Active
          <input type="checkbox" name="isActive" [(ngModel)]="editor.isActive">
        </label>
        <label class="col-span-2">
          Short Description
          <input name="shortDescription" [(ngModel)]="editor.shortDescription">
        </label>
        <label class="col-span-2">
          Full Description
          <textarea name="fullDescription" [(ngModel)]="editor.fullDescription"></textarea>
        </label>
        <label class="col-span-2">
          Technical Specifications
          <textarea name="technicalSpecifications" [(ngModel)]="editor.technicalSpecifications"></textarea>
        </label>
        <label class="col-span-2">
          Additional Information
          <textarea name="additionalInformation" [(ngModel)]="editor.additionalInformation"></textarea>
        </label>

        <div class="button-row col-span-2">
          <button class="btn primary" type="submit">{{ editor.id ? 'Update product' : 'Create product' }}</button>
          <button class="btn secondary" type="button" (click)="resetEditor()" *ngIf="editor.id">Reset</button>
          <a class="btn secondary" *ngIf="selectedProduct" [routerLink]="['/products', selectedProduct.slug]" target="_blank">Public page</a>
        </div>
      </form>
      <p *ngIf="message" class="status">{{ message }}</p>
    </section>

    <section class="panel printable" style="margin-top: 1rem;">
      <div class="page-head" style="padding: 0; border: 0; box-shadow: none; margin-bottom: 0.75rem;">
        <div>
          <h2>QR label</h2>
          <p class="lead" *ngIf="selectedProduct">Current: {{ selectedProduct.name }}</p>
        </div>
        <div class="button-row" style="margin-top: 0;">
          <button class="btn secondary" type="button" (click)="generateQr()" [disabled]="!selectedProduct">Generate QR</button>
          <button class="btn primary" type="button" (click)="printQr()" [disabled]="!qrCode">Print label</button>
        </div>
      </div>

      <div class="grid-form" *ngIf="selectedProduct">
        <label>
          Label title
          <input name="labelTitle" [(ngModel)]="qrRequest.labelTitle">
        </label>
        <label>
          Label description
          <input name="labelDescription" [(ngModel)]="qrRequest.labelDescription">
        </label>
        <label>
          Batch number
          <input name="batchNumber" [(ngModel)]="qrRequest.batchNumber">
        </label>
        <label>
          Serial number
          <input name="serialNumber" [(ngModel)]="qrRequest.serialNumber">
        </label>
        <label>
          Manufacturing date
          <input type="date" name="manufacturingDate" [(ngModel)]="qrRequest.manufacturingDate">
        </label>
        <label>
          Expiry date
          <input type="date" name="expiryDate" [(ngModel)]="qrRequest.expiryDate">
        </label>
        <label class="col-span-2">
          Custom note
          <input name="customNote" [(ngModel)]="qrRequest.customNote">
        </label>
      </div>

      <div class="qr-label" id="qr-label-preview" *ngIf="selectedProduct">
        <div>
          <p class="eyebrow">Product label</p>
          <h2>{{ labelTitle() }}</h2>
          <p class="lead">{{ labelDescription() }}</p>
        </div>
        <div class="qr-label-body" *ngIf="qrCode; else noQr">
          <img class="qr-preview" [src]="api.imageUrl(qrCode.qrCodeImagePath)" [alt]="'QR code for ' + selectedProduct.name">
          <dl class="detail-list">
            <div><dt>Public link</dt><dd><a [href]="qrCode.qrCodeUrl" target="_blank">{{ qrCode.qrCodeUrl }}</a></dd></div>
            <div><dt>Product</dt><dd>{{ selectedProduct.name }}</dd></div>
            <div><dt>Product code</dt><dd>{{ selectedProduct.productCode }}</dd></div>
            <div><dt>Destination</dt><dd>Public product page</dd></div>
          </dl>
        </div>
        <ng-template #noQr>
          <p class="lead">No QR code generated yet.</p>
        </ng-template>
      </div>
    </section>

    <section class="panel" style="margin-top: 1rem;">
      <div class="page-head" style="padding: 0; border: 0; box-shadow: none; margin-bottom: 0.75rem;">
        <div>
          <h2>Product images</h2>
          <p class="lead" *ngIf="selectedUploadProduct">
            {{ selectedUploadProduct.name }} - {{ selectedUploadProduct.images.length }}/{{ maxProductImages }}
          </p>
        </div>
      </div>
      <div class="grid-form">
        <label>
          Product ID
          <input type="number" name="selectedProductId" [(ngModel)]="selectedProductId" (ngModelChange)="syncSelectedUploadProduct()">
        </label>
        <label>
          Alt text
          <input name="imageAltText" [(ngModel)]="imageAltText">
        </label>
        <label>
          Sort order
          <input type="number" name="imageSortOrder" [(ngModel)]="imageSortOrder">
        </label>
        <label>
          Main image
          <input type="checkbox" name="imageIsMain" [(ngModel)]="imageIsMain">
        </label>
        <label class="col-span-2">
          Images
          <input type="file" accept="image/*" multiple (change)="handleFiles($event)" [disabled]="remainingImageSlots() === 0">
        </label>
      </div>
      <div class="admin-image-strip" *ngIf="selectedUploadProduct?.images?.length">
        <div class="admin-image-item" *ngFor="let image of selectedUploadProductImages()">
          <div class="admin-image-frame">
            <img [src]="api.imageUrl(image.imageUrl)" [alt]="image.altText || selectedUploadProduct?.name || 'Product image'">
          </div>
          <button class="btn danger mini" type="button" (click)="deleteImage(image.id)">Delete</button>
        </div>
      </div>
      <p class="muted upload-count" *ngIf="selectedFiles.length > 0">
        Selected {{ selectedFiles.length }} image{{ selectedFiles.length === 1 ? '' : 's' }}.
      </p>
      <div class="button-row">
        <button class="btn secondary" type="button" (click)="uploadImages()" [disabled]="selectedFiles.length === 0 || remainingImageSlots() === 0">
          Upload selected images
        </button>
      </div>
    </section>

    <section class="panel" style="margin-top: 1rem;">
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Name</th><th>Category</th><th>SKU</th><th>Active</th><th></th></tr>
          </thead>
          <tbody>
            <tr *ngFor="let product of products">
              <td>{{ product.name }}</td>
              <td>{{ product.categoryName }}</td>
              <td>{{ product.sku }}</td>
              <td>{{ product.isActive }}</td>
              <td>
                <div class="button-row" style="margin-top: 0;">
                  <button class="btn secondary" type="button" (click)="edit(product)">Edit</button>
                  <button class="btn secondary" type="button" (click)="openQr(product)">QR</button>
                  <a class="btn secondary" [routerLink]="['/products', product.slug]" target="_blank">Public</a>
                  <button class="btn danger" type="button" (click)="deleteProduct(product.id)">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  `
})
export class AdminProductsPageComponent implements OnInit {
  products: ProductDto[] = [];
  categories: CategoryDto[] = [];
  editor: ProductUpsertRequest = this.emptyEditor();
  selectedProduct: ProductDto | null = null;
  selectedUploadProduct: ProductDto | null = null;
  selectedProductId = 0;
  selectedFiles: File[] = [];
  imageAltText = '';
  imageSortOrder = 0;
  imageIsMain = false;
  readonly maxProductImages = 5;
  qrRequest: QrCodeCreateRequest = {};
  qrCode: ProductQrCodeDto | null = null;
  message = '';

  constructor(public readonly api: ApiService) {}

  async ngOnInit(): Promise<void> {
    await this.load();
  }

  async load(): Promise<void> {
    this.categories = await firstValueFrom(this.api.getCategories());
    this.products = await firstValueFrom(this.api.getProducts());
    if (this.selectedProduct) {
      this.selectedProduct = this.products.find((product) => product.id === this.selectedProduct?.id) ?? this.selectedProduct;
    }
    this.syncSelectedUploadProduct();
  }

  async save(): Promise<void> {
    if (this.editor.categoryId === 0) {
      this.message = 'Choose a category.';
      return;
    }

    try {
      const saved = this.editor.id
        ? await firstValueFrom(this.api.updateProduct(this.editor.id, this.editor))
        : await firstValueFrom(this.api.createProduct(this.editor));

      this.selectedProduct = saved;
      this.selectedProductId = saved.id;
      this.qrCode = null;
      this.qrRequest = {
        labelTitle: saved.name,
        labelDescription: saved.shortDescription,
        customNote: 'Scan the QR code to open the product page.'
      };
      this.message = this.editor.id ? 'Product updated.' : 'Product created.';
      this.resetEditor();
      await this.load();
    } catch {
      this.message = 'Could not save the product.';
    }
  }

  edit(product: ProductDto): void {
    this.editor = {
      id: product.id,
      categoryId: product.categoryId,
      name: product.name,
      slug: product.slug,
      sku: product.sku,
      shortDescription: product.shortDescription,
      fullDescription: product.fullDescription,
      brand: product.brand,
      model: product.model,
      stockQuantity: product.stockQuantity,
      productCode: product.productCode,
      barcode: product.barcode,
      weight: product.weight,
      dimensions: product.dimensions,
      material: product.material,
      color: product.color,
      warrantyInfo: product.warrantyInfo,
      technicalSpecifications: product.technicalSpecifications,
      additionalInformation: product.additionalInformation,
      metaTitle: product.metaTitle,
      metaDescription: product.metaDescription,
      isActive: product.isActive,
      isFeatured: product.isFeatured
    };
    this.openQr(product);
  }

  async deleteProduct(id: number): Promise<void> {
    try {
      await firstValueFrom(this.api.deleteProduct(id));
      this.message = 'Product deleted.';
      if (this.selectedProduct?.id === id) {
        this.selectedProduct = null;
        this.qrCode = null;
      }
      if (this.selectedUploadProduct?.id === id) {
        this.selectedUploadProduct = null;
        this.selectedFiles = [];
      }
      await this.load();
    } catch {
      this.message = 'Could not delete the product.';
    }
  }

  openQr(product: ProductDto): void {
    this.selectedProduct = product;
    this.selectedUploadProduct = product;
    this.selectedProductId = product.id;
    this.qrCode = null;
    this.qrRequest = {
      labelTitle: product.name,
      labelDescription: product.shortDescription,
      customNote: 'Scan the QR code to open the product page.'
    };
  }

  async generateQr(): Promise<void> {
    if (!this.selectedProduct) {
      this.message = 'Choose a product first.';
      return;
    }

    try {
      this.qrCode = await firstValueFrom(this.api.createQrCode(this.selectedProduct.id, this.qrRequest));
      this.message = 'QR label generated.';
    } catch {
      this.message = 'QR generation failed.';
    }
  }

  printQr(): void {
    if (!this.qrCode || !this.selectedProduct) {
      return;
    }

    const imageUrl = this.escapeHtml(this.api.imageUrl(this.qrCode.qrCodeImagePath));
    const title = this.escapeHtml(this.labelTitle());
    const printWindow = window.open('', '_blank', 'width=900,height=650');
    if (!printWindow) {
      this.message = 'Could not open the print window.';
      return;
    }

    printWindow.document.write(`
      <html>
        <head>
          <title>${title}</title>
          <style>
            @page { margin: 0; }
            body {
              align-items: center;
              background: #ffffff;
              display: flex;
              justify-content: center;
              margin: 0;
              min-height: 100vh;
            }
            img {
              display: block;
              height: auto;
              max-width: 100vw;
              width: 320px;
            }
          </style>
        </head>
        <body>
          <img src="${imageUrl}" alt="${title}">
        </body>
      </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
  }

  syncSelectedUploadProduct(): void {
    const id = Number(this.selectedProductId) || 0;
    this.selectedUploadProduct = this.products.find((product) => product.id === id) ?? null;
    this.selectedFiles = this.selectedFiles.slice(0, this.remainingImageSlots());
  }

  selectedUploadProductImages(): ProductImageDto[] {
    return [...(this.selectedUploadProduct?.images ?? [])].sort((a, b) => {
      if (a.isMainImage !== b.isMainImage) {
        return a.isMainImage ? -1 : 1;
      }

      return a.sortOrder - b.sortOrder || a.id - b.id;
    });
  }

  remainingImageSlots(): number {
    return Math.max(0, this.maxProductImages - (this.selectedUploadProduct?.images.length ?? 0));
  }

  handleFiles(event: Event): void {
    const input = event.target as HTMLInputElement;
    const files = Array.from(input.files ?? []);
    const remaining = this.remainingImageSlots();

    if (remaining === 0) {
      this.selectedFiles = [];
      this.message = 'This product already has 5 images.';
      return;
    }

    this.selectedFiles = files.slice(0, remaining);
    if (files.length > remaining) {
      this.message = `Only ${remaining} more image${remaining === 1 ? '' : 's'} can be uploaded for this product.`;
    }
  }

  async uploadImages(): Promise<void> {
    if (this.selectedFiles.length === 0 || this.selectedProductId === 0) {
      this.message = 'Choose a product and at least one image first.';
      return;
    }

    const remaining = this.remainingImageSlots();
    if (remaining === 0 || this.selectedFiles.length > remaining) {
      this.message = 'A product can have up to 5 images.';
      return;
    }

    try {
      for (const [index, file] of this.selectedFiles.entries()) {
        await firstValueFrom(this.api.uploadProductImage(
          this.selectedProductId,
          file,
          this.imageAltText,
          this.imageIsMain && index === 0,
          this.imageSortOrder + index
        ));
      }

      const uploadedCount = this.selectedFiles.length;
      this.message = `${uploadedCount} image${uploadedCount === 1 ? '' : 's'} uploaded.`;
      this.selectedFiles = [];
      await this.load();
    } catch {
      this.message = 'Upload failed.';
    }
  }

  async deleteImage(imageId: number): Promise<void> {
    try {
      await firstValueFrom(this.api.deleteProductImage(imageId));
      this.message = 'Image deleted.';
      await this.load();
    } catch {
      this.message = 'Could not delete the image.';
    }
  }

  resetEditor(): void {
    this.editor = this.emptyEditor();
  }

  labelTitle(): string {
    return this.qrRequest.labelTitle?.trim() || this.selectedProduct?.name || 'Product label';
  }

  labelDescription(): string {
    return this.qrRequest.labelDescription?.trim() || this.selectedProduct?.shortDescription || 'Scan to view the product details.';
  }

  private emptyEditor(): ProductUpsertRequest {
    return {
      categoryId: 0,
      name: '',
      slug: '',
      sku: '',
      shortDescription: '',
      fullDescription: '',
      brand: '',
      model: '',
      stockQuantity: null,
      productCode: '',
      barcode: '',
      weight: null,
      dimensions: '',
      material: '',
      color: '',
      warrantyInfo: '',
      technicalSpecifications: '',
      additionalInformation: '',
      metaTitle: '',
      metaDescription: '',
      isActive: true,
      isFeatured: false
    };
  }

  private escapeHtml(value: string): string {
    return value
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
}
