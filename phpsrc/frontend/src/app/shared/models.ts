export interface CategoryDto {
  id: number;
  name: string;
  slug: string;
  description?: string | null;
  parentCategoryId?: number | null;
  imageUrl?: string | null;
  isActive: boolean;
}

export interface CategoryUpsertRequest {
  id?: number;
  name: string;
  description?: string | null;
  parentCategoryId?: number | null;
  imageUrl?: string | null;
  isActive: boolean;
}

export interface ProductImageDto {
  id: number;
  productId: number;
  imageUrl: string;
  altText?: string | null;
  sortOrder: number;
  isMainImage: boolean;
}

export interface ProductDto {
  id: number;
  categoryId: number;
  categoryName: string;
  name: string;
  slug: string;
  sku: string;
  shortDescription: string;
  fullDescription: string;
  brand: string;
  model: string;
  price?: number | null;
  currency?: string | null;
  stockQuantity?: number | null;
  productCode: string;
  barcode?: string | null;
  weight?: number | null;
  dimensions?: string | null;
  material?: string | null;
  color?: string | null;
  warrantyInfo?: string | null;
  technicalSpecifications?: string | null;
  additionalInformation?: string | null;
  metaTitle?: string | null;
  metaDescription?: string | null;
  isActive: boolean;
  isFeatured: boolean;
  images: ProductImageDto[];
}

export interface ProductUpsertRequest {
  id?: number;
  categoryId: number;
  name: string;
  slug?: string | null;
  sku: string;
  shortDescription: string;
  fullDescription: string;
  brand: string;
  model: string;
  price?: number | null;
  currency?: string | null;
  stockQuantity?: number | null;
  productCode: string;
  barcode?: string | null;
  weight?: number | null;
  dimensions?: string | null;
  material?: string | null;
  color?: string | null;
  warrantyInfo?: string | null;
  technicalSpecifications?: string | null;
  additionalInformation?: string | null;
  metaTitle?: string | null;
  metaDescription?: string | null;
  isActive: boolean;
  isFeatured: boolean;
}

export interface PublicProductDto {
  name: string;
  slug: string;
  categoryName: string;
  sku: string;
  productCode: string;
  shortDescription: string;
  fullDescription: string;
  brand: string;
  model: string;
  price?: number | null;
  currency?: string | null;
  warrantyInfo?: string | null;
  technicalSpecifications?: string | null;
  additionalInformation?: string | null;
  images: ProductImageDto[];
}

export interface QrCodeCreateRequest {
  labelTitle?: string | null;
  labelDescription?: string | null;
  batchNumber?: string | null;
  serialNumber?: string | null;
  manufacturingDate?: string | null;
  expiryDate?: string | null;
  customNote?: string | null;
}

export interface ProductQrCodeDto extends QrCodeCreateRequest {
  id: number;
  productId: number;
  qrCodeUrl: string;
  qrCodeImagePath: string;
}

export interface CompanyInfoDto {
  companyName: string;
  description: string;
  mission: string;
  vision: string;
  services: string;
  contactInformation: string;
  address: string;
  email: string;
  phoneNumber: string;
  socialMediaLinks?: string | null;
}

export interface LoginRequest {
  email: string;
  password: string;
  captchaToken: string;
  captchaAnswer: string;
}

export interface AuthResponse {
  accessToken: string;
  fullName: string;
  email: string;
  role: string;
}

export interface CaptchaChallenge {
  question: string;
  token: string;
  expiresIn: number;
}
