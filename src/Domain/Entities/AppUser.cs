namespace QrCode.Domain.Entities;

public sealed class AppUser : BaseEntity
{
    public string FullName { get; set; } = string.Empty;

    public string Email { get; set; } = string.Empty;

    public string PasswordHash { get; set; } = string.Empty;

    public string Role { get; set; } = "Admin";

    public bool IsActive { get; set; } = true;
}
