import { Routes } from '@angular/router';
import { adminGuard } from './shared/admin.guard';
import { AboutPageComponent } from './pages/about-page.component';
import { AdminCategoriesPageComponent } from './pages/admin-categories-page.component';
import { AdminDashboardPageComponent } from './pages/admin-dashboard-page.component';
import { AdminProductsPageComponent } from './pages/admin-products-page.component';
import { AdminShellPageComponent } from './pages/admin-shell-page.component';
import { LoginPageComponent } from './pages/login-page.component';
import { ProductDetailPageComponent } from './pages/product-detail-page.component';
import { ProductsPageComponent } from './pages/products-page.component';

export const routes: Routes = [
  { path: '', component: ProductsPageComponent },
  { path: 'products', component: ProductsPageComponent },
  { path: 'products/:slug', component: ProductDetailPageComponent },
  { path: 'about', component: AboutPageComponent },
  { path: 'admin/login', component: LoginPageComponent },
  { path: 'admin/dashboard', redirectTo: 'admin/home', pathMatch: 'full' },
  {
    path: 'admin',
    component: AdminShellPageComponent,
    canActivate: [adminGuard],
    children: [
      { path: '', redirectTo: 'home', pathMatch: 'full' },
      { path: 'home', component: AdminDashboardPageComponent },
      { path: 'products', component: AdminProductsPageComponent },
      { path: 'categories', component: AdminCategoriesPageComponent }
    ]
  },
  { path: '**', redirectTo: '' }
];
