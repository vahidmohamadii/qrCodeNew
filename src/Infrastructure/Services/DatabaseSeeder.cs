using Microsoft.EntityFrameworkCore;
using QrCode.Domain.Entities;
using QrCode.Infrastructure.Data;

namespace QrCode.Infrastructure.Services;

public interface IDatabaseSeeder
{
    Task SeedAsync(CancellationToken cancellationToken = default);
}

internal sealed class DatabaseSeeder : IDatabaseSeeder
{
    private readonly AppDbContext _dbContext;

    public DatabaseSeeder(AppDbContext dbContext)
    {
        _dbContext = dbContext;
    }

    public async Task SeedAsync(CancellationToken cancellationToken = default)
    {
        await _dbContext.Database.EnsureCreatedAsync(cancellationToken);

        const string adminEmail = "admin@example.com";
        const string adminPassword = "Admin123!";

        var adminUser = await _dbContext.Users.FirstOrDefaultAsync(
            x => x.Email == adminEmail,
            cancellationToken);

        if (adminUser is null)
        {
            _dbContext.Users.Add(new AppUser
            {
                FullName = "Administrator",
                Email = adminEmail,
                PasswordHash = SecurityHelpers.HashPassword(adminPassword),
                Role = "Admin",
                IsActive = true
            });
        }
        else
        {
            adminUser.FullName = "Administrator";
            adminUser.PasswordHash = SecurityHelpers.HashPassword(adminPassword);
            adminUser.Role = "Admin";
            adminUser.IsActive = true;
        }

        var companyInfo = await _dbContext.CompanyInfos.FirstOrDefaultAsync(x => x.Id == 1, cancellationToken);
        if (companyInfo is not null)
        {
            companyInfo.CompanyName = "Namelenam";
        }

        if (!await _dbContext.Categories.AnyAsync(cancellationToken))
        {
            var category = new Category
            {
                Name = "Default Category",
                Slug = SecurityHelpers.Slugify("Default Category"),
                Description = "Seeded category",
                IsActive = true
            };
            _dbContext.Categories.Add(category);

            _dbContext.Products.Add(new Product
            {
                Category = category,
                Name = "Sample Product",
                Slug = SecurityHelpers.Slugify("Sample Product"),
                SKU = "SKU-001",
                ShortDescription = "Sample public product",
                FullDescription = "This is a seeded sample product.",
                Brand = "QrCode",
                Model = "Demo",
                ProductCode = "P-001",
                Currency = "USD",
                Price = 10,
                IsActive = true
            });
        }

        await _dbContext.SaveChangesAsync(cancellationToken);
    }
}
