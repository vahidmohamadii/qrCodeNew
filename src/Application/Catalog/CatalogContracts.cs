namespace QrCode.Application.Catalog;

public sealed class CategoryDto
{
    public int Id { get; set; }

    public string Name { get; set; } = string.Empty;

    public string Slug { get; set; } = string.Empty;

    public string? Description { get; set; }

    public int? ParentCategoryId { get; set; }

    public string? ImageUrl { get; set; }

    public bool IsActive { get; set; }
}

public sealed class CategoryUpsertRequest
{
    public int Id { get; set; }

    public string Name { get; set; } = string.Empty;

    public string? Description { get; set; }

    public int? ParentCategoryId { get; set; }

    public string? ImageUrl { get; set; }

    public bool IsActive { get; set; } = true;
}

public sealed class ProductImageDto
{
    public int Id { get; set; }

    public int ProductId { get; set; }

    public string ImageUrl { get; set; } = string.Empty;

    public string? AltText { get; set; }

    public int SortOrder { get; set; }

    public bool IsMainImage { get; set; }
}

public sealed class ProductDto
{
    public int Id { get; set; }

    public int CategoryId { get; set; }

    public string CategoryName { get; set; } = string.Empty;

    public string Name { get; set; } = string.Empty;

    public string Slug { get; set; } = string.Empty;

    public string SKU { get; set; } = string.Empty;

    public string ShortDescription { get; set; } = string.Empty;

    public string FullDescription { get; set; } = string.Empty;

    public string Brand { get; set; } = string.Empty;

    public string Model { get; set; } = string.Empty;

    public decimal? Price { get; set; }

    public string? Currency { get; set; }

    public int? StockQuantity { get; set; }

    public string ProductCode { get; set; } = string.Empty;

    public string? Barcode { get; set; }

    public decimal? Weight { get; set; }

    public string? Dimensions { get; set; }

    public string? Material { get; set; }

    public string? Color { get; set; }

    public string? WarrantyInfo { get; set; }

    public string? TechnicalSpecifications { get; set; }

    public string? AdditionalInformation { get; set; }

    public string? MetaTitle { get; set; }

    public string? MetaDescription { get; set; }

    public bool IsActive { get; set; }

    public bool IsFeatured { get; set; }

    public List<ProductImageDto> Images { get; set; } = new();
}

public sealed class ProductUpsertRequest
{
    public int Id { get; set; }

    public int CategoryId { get; set; }

    public string Name { get; set; } = string.Empty;

    public string? Slug { get; set; }

    public string SKU { get; set; } = string.Empty;

    public string ShortDescription { get; set; } = string.Empty;

    public string FullDescription { get; set; } = string.Empty;

    public string Brand { get; set; } = string.Empty;

    public string Model { get; set; } = string.Empty;

    public decimal? Price { get; set; }

    public string? Currency { get; set; }

    public int? StockQuantity { get; set; }

    public string ProductCode { get; set; } = string.Empty;

    public string? Barcode { get; set; }

    public decimal? Weight { get; set; }

    public string? Dimensions { get; set; }

    public string? Material { get; set; }

    public string? Color { get; set; }

    public string? WarrantyInfo { get; set; }

    public string? TechnicalSpecifications { get; set; }

    public string? AdditionalInformation { get; set; }

    public string? MetaTitle { get; set; }

    public string? MetaDescription { get; set; }

    public bool IsActive { get; set; } = true;

    public bool IsFeatured { get; set; }
}

public sealed class PublicProductDto
{
    public string Name { get; set; } = string.Empty;

    public string Slug { get; set; } = string.Empty;

    public string CategoryName { get; set; } = string.Empty;

    public string SKU { get; set; } = string.Empty;

    public string ProductCode { get; set; } = string.Empty;

    public string ShortDescription { get; set; } = string.Empty;

    public string FullDescription { get; set; } = string.Empty;

    public string Brand { get; set; } = string.Empty;

    public string Model { get; set; } = string.Empty;

    public decimal? Price { get; set; }

    public string? Currency { get; set; }

    public string? WarrantyInfo { get; set; }

    public string? TechnicalSpecifications { get; set; }

    public string? AdditionalInformation { get; set; }

    public List<ProductImageDto> Images { get; set; } = new();
}

public sealed class ProductQrCodeDto
{
    public int Id { get; set; }

    public int ProductId { get; set; }

    public string QrCodeUrl { get; set; } = string.Empty;

    public string QrCodeImagePath { get; set; } = string.Empty;

    public string? LabelTitle { get; set; }

    public string? LabelDescription { get; set; }

    public string? BatchNumber { get; set; }

    public string? SerialNumber { get; set; }

    public DateTime? ManufacturingDate { get; set; }

    public DateTime? ExpiryDate { get; set; }

    public string? CustomNote { get; set; }
}

public sealed class QrCodeCreateRequest
{
    public string? LabelTitle { get; set; }

    public string? LabelDescription { get; set; }

    public string? BatchNumber { get; set; }

    public string? SerialNumber { get; set; }

    public DateTime? ManufacturingDate { get; set; }

    public DateTime? ExpiryDate { get; set; }

    public string? CustomNote { get; set; }
}
