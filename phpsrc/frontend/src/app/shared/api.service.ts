import { HttpClient, HttpErrorResponse, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { catchError, map, Observable, throwError } from 'rxjs';
import { environment } from '../../environments/environment';
import {
  AuthResponse,
  CaptchaChallenge,
  CategoryDto,
  CategoryUpsertRequest,
  CompanyInfoDto,
  LoginRequest,
  ProductDto,
  ProductImageDto,
  ProductQrCodeDto,
  ProductUpsertRequest,
  PublicProductDto,
  QrCodeCreateRequest
} from './models';
import { AuthService } from './auth.service';

@Injectable({ providedIn: 'root' })
export class ApiService {
  private readonly baseUrl = environment.apiBaseUrl.replace(/\/$/, '');
  private readonly placeholder =
    'data:image/svg+xml;utf8,' +
    encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 480"><rect width="640" height="480" fill="#dce4ea"/><path d="M120 340h400L410 210l-80 90-55-65z" fill="#8da1b1"/><circle cx="230" cy="175" r="45" fill="#f8fafc"/></svg>');

  constructor(private readonly http: HttpClient, private readonly auth: AuthService) {}

  getProducts(search?: string, categoryId?: number | null): Observable<ProductDto[]> {
    let params = new HttpParams();
    if (search?.trim()) {
      params = params.set('search', search.trim());
    }

    if (categoryId) {
      params = params.set('categoryId', categoryId);
    }

    return this.http.get<ProductDto[]>(`${this.baseUrl}/api/products`, { params });
  }

  getProductById(id: number): Observable<ProductDto> {
    return this.http.get<ProductDto>(`${this.baseUrl}/api/products/${id}`);
  }

  getProductBySlug(slug: string): Observable<PublicProductDto> {
    return this.http.get<PublicProductDto>(`${this.baseUrl}/api/products/by-slug/${encodeURIComponent(slug)}`);
  }

  createProduct(request: ProductUpsertRequest): Observable<ProductDto> {
    return this.http.post<ProductDto>(`${this.baseUrl}/api/products`, request, { headers: this.auth.authHeaders() });
  }

  updateProduct(id: number, request: ProductUpsertRequest): Observable<ProductDto> {
    return this.http.put<ProductDto>(`${this.baseUrl}/api/products/${id}`, request, { headers: this.auth.authHeaders() });
  }

  deleteProduct(id: number): Observable<void> {
    return this.http.delete<void>(`${this.baseUrl}/api/products/${id}`, { headers: this.auth.authHeaders() });
  }

  uploadProductImage(productId: number, file: File, altText: string, isMainImage: boolean, sortOrder: number): Observable<ProductImageDto> {
    const form = new FormData();
    form.append('image', file, file.name);
    form.append('altText', altText);
    form.append('isMainImage', String(isMainImage));
    form.append('sortOrder', String(sortOrder));

    return this.http.post<ProductImageDto>(`${this.baseUrl}/api/products/${productId}/images`, form, { headers: this.auth.authHeaders() });
  }

  deleteProductImage(imageId: number): Observable<void> {
    return this.http.delete<void>(`${this.baseUrl}/api/products/images/${imageId}`, { headers: this.auth.authHeaders() });
  }

  createQrCode(productId: number, request: QrCodeCreateRequest): Observable<ProductQrCodeDto> {
    return this.http.post<ProductQrCodeDto>(`${this.baseUrl}/api/products/${productId}/qr-code`, request, { headers: this.auth.authHeaders() });
  }

  getCategories(search?: string): Observable<CategoryDto[]> {
    const params = search?.trim() ? new HttpParams().set('search', search.trim()) : undefined;
    return this.http.get<CategoryDto[]>(`${this.baseUrl}/api/categories`, { params });
  }

  createCategory(request: CategoryUpsertRequest): Observable<CategoryDto> {
    return this.http.post<CategoryDto>(`${this.baseUrl}/api/categories`, request, { headers: this.auth.authHeaders() });
  }

  updateCategory(id: number, request: CategoryUpsertRequest): Observable<CategoryDto> {
    return this.http.put<CategoryDto>(`${this.baseUrl}/api/categories/${id}`, request, { headers: this.auth.authHeaders() });
  }

  deleteCategory(id: number): Observable<void> {
    return this.http.delete<void>(`${this.baseUrl}/api/categories/${id}`, { headers: this.auth.authHeaders() });
  }

  getCompanyInfo(): Observable<CompanyInfoDto> {
    return this.http.get<CompanyInfoDto>(`${this.baseUrl}/api/company-info`);
  }

  updateCompanyInfo(request: CompanyInfoDto): Observable<CompanyInfoDto> {
    return this.http.put<CompanyInfoDto>(`${this.baseUrl}/api/company-info`, request, { headers: this.auth.authHeaders() });
  }

  uploadHomeHeroImage(file: File): Observable<CompanyInfoDto> {
    const form = new FormData();
    form.append('image', file, file.name);

    return this.http.post<CompanyInfoDto>(`${this.baseUrl}/api/company-info/home-hero-image`, form, { headers: this.auth.authHeaders() });
  }

  getCaptcha(): Observable<CaptchaChallenge> {
    return this.http.get<CaptchaChallenge>(`${this.baseUrl}/api/auth/captcha`);
  }

  login(request: LoginRequest): Observable<AuthResponse> {
    return this.http.post(`${this.baseUrl}/api/auth/login`, request, { observe: 'response', responseType: 'text' }).pipe(
      map((response) => {
        const body = response.body?.trim() ?? '';

        try {
          const auth = JSON.parse(body) as Partial<AuthResponse>;
          if (!auth.accessToken || !auth.fullName || !auth.email || !auth.role) {
            throw new Error('missing-fields');
          }

          const result = auth as AuthResponse;
          this.auth.setSession(result);
          return result;
        } catch {
          if (body.startsWith('<!doctype') || body.startsWith('<html')) {
            throw new Error('Login API returned HTML instead of JSON. Check cPanel routing and api.php deployment.');
          }

          throw new Error('Login API returned an unexpected response.');
        }
      }),
      catchError((error) => {
        if (!(error instanceof HttpErrorResponse)) {
          return throwError(() => error);
        }

        let message = 'Login failed.';
        const body = typeof error.error === 'string' ? error.error.trim() : error.error;

        if (typeof body === 'string' && body !== '') {
          try {
            const parsed = JSON.parse(body) as { message?: string };
            message = parsed.message || message;
          } catch {
            message = body.startsWith('<!doctype') || body.startsWith('<html')
              ? 'Login API returned HTML instead of JSON. Check cPanel routing and api.php deployment.'
              : body;
          }
        } else if (body && typeof body === 'object' && 'message' in body) {
          message = String(body.message);
        }

        return throwError(() => new Error(message));
      })
    );
  }

  imageUrl(path?: string | null): string {
    if (!path) {
      return this.placeholder;
    }

    return /^https?:\/\//i.test(path) || path.startsWith('data:') ? path : `${this.baseUrl}${path}`;
  }
}
