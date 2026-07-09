import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { firstValueFrom } from 'rxjs';
import { ApiService } from '../shared/api.service';
import { CategoryDto, CategoryUpsertRequest } from '../shared/models';

@Component({
  selector: 'app-admin-categories-page',
  standalone: true,
  imports: [CommonModule, FormsModule],
  template: `
    <section class="page-head">
      <div>
        <p class="eyebrow">Catalog</p>
        <h2>Product categories</h2>
        <p class="lead">Create and maintain the categories that products can be assigned to.</p>
      </div>
    </section>

    <section class="panel">
      <form (ngSubmit)="save()" class="grid-form">
        <label>
          Name
          <input name="name" [(ngModel)]="editor.name" required>
        </label>
        <label>
          Description
          <input name="description" [(ngModel)]="editor.description">
        </label>
        <label>
          Active
          <input type="checkbox" name="isActive" [(ngModel)]="editor.isActive">
        </label>
        <div class="button-row">
          <button class="btn primary" type="submit">{{ editor.id ? 'Update' : 'Create' }}</button>
          <button class="btn secondary" type="button" (click)="reset()" *ngIf="editor.id">Cancel</button>
        </div>
      </form>
      <p *ngIf="message" class="status">{{ message }}</p>
    </section>

    <section class="panel" style="margin-top: 1rem;">
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Name</th><th>Slug</th><th>Active</th><th></th></tr>
          </thead>
          <tbody>
            <tr *ngFor="let category of categories">
              <td>{{ category.name }}</td>
              <td>{{ category.slug }}</td>
              <td>{{ category.isActive }}</td>
              <td>
                <div class="button-row" style="margin-top: 0;">
                  <button class="btn secondary" type="button" (click)="edit(category)">Edit</button>
                  <button class="btn danger" type="button" (click)="deleteCategory(category.id)">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  `
})
export class AdminCategoriesPageComponent implements OnInit {
  categories: CategoryDto[] = [];
  editor: CategoryUpsertRequest = this.emptyEditor();
  message = '';

  constructor(private readonly api: ApiService) {}

  async ngOnInit(): Promise<void> {
    await this.load();
  }

  async load(): Promise<void> {
    this.categories = await firstValueFrom(this.api.getCategories());
  }

  async save(): Promise<void> {
    try {
      if (this.editor.id) {
        await firstValueFrom(this.api.updateCategory(this.editor.id, this.editor));
        this.message = 'Category updated.';
      } else {
        await firstValueFrom(this.api.createCategory(this.editor));
        this.message = 'Category created.';
      }

      this.reset();
      await this.load();
    } catch {
      this.message = 'Could not save the category.';
    }
  }

  edit(category: CategoryDto): void {
    this.editor = {
      id: category.id,
      name: category.name,
      description: category.description,
      parentCategoryId: category.parentCategoryId,
      imageUrl: category.imageUrl,
      isActive: category.isActive
    };
  }

  async deleteCategory(id: number): Promise<void> {
    try {
      await firstValueFrom(this.api.deleteCategory(id));
      this.message = 'Category deleted.';
      await this.load();
    } catch {
      this.message = 'Could not delete the category.';
    }
  }

  reset(): void {
    this.editor = this.emptyEditor();
  }

  private emptyEditor(): CategoryUpsertRequest {
    return { name: '', description: '', isActive: true };
  }
}
