namespace QrCode.Domain.Entities;

public sealed class Product : BaseEntity
{
    public int CategoryId { get; set; }

    public Category? Category { get; set; }

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

    public bool IsActive { get; set; } = true;

    public bool IsFeatured { get; set; }

    public ICollection<ProductImage> Images { get; set; } = new List<ProductImage>();

    public ICollection<ProductQrCode> QrCodes { get; set; } = new List<ProductQrCode>();
}
