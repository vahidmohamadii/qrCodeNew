namespace QrCode.Domain.Entities;

public sealed class ProductQrCode : BaseEntity
{
    public int ProductId { get; set; }

    public Product? Product { get; set; }

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
