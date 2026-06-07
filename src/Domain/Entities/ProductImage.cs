namespace QrCode.Domain.Entities;

public sealed class ProductImage : BaseEntity
{
    public int ProductId { get; set; }

    public Product? Product { get; set; }

    public string ImageUrl { get; set; } = string.Empty;

    public string? AltText { get; set; }

    public int SortOrder { get; set; }

    public bool IsMainImage { get; set; }
}
