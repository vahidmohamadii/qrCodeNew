namespace QrCode.Domain.Entities;

public sealed class QrCodeLabelRequest
{
    public int ProductId { get; set; }

    public string? LabelTitle { get; set; }

    public string? LabelDescription { get; set; }

    public string? BatchNumber { get; set; }

    public string? SerialNumber { get; set; }

    public DateTime? ManufacturingDate { get; set; }

    public DateTime? ExpiryDate { get; set; }

    public string? CustomNote { get; set; }
}
