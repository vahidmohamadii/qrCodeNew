namespace QrCode.Domain.Entities;

public sealed class CompanyInfo : BaseEntity
{
    public string CompanyName { get; set; } = string.Empty;

    public string Description { get; set; } = string.Empty;

    public string Mission { get; set; } = string.Empty;

    public string Vision { get; set; } = string.Empty;

    public string Services { get; set; } = string.Empty;

    public string ContactInformation { get; set; } = string.Empty;

    public string Address { get; set; } = string.Empty;

    public string Email { get; set; } = string.Empty;

    public string PhoneNumber { get; set; } = string.Empty;

    public string? SocialMediaLinks { get; set; }
}
