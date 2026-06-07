using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using QrCode.Application.Abstractions;
using QrCode.Infrastructure.Data;
using QrCode.Infrastructure.Services;

namespace QrCode.Infrastructure;

public static class InfrastructureServiceCollectionExtensions
{
    public static IServiceCollection AddInfrastructure(this IServiceCollection services, IConfiguration configuration, string? webRootPath = null)
    {
        var connectionString = configuration.GetConnectionString("DefaultConnection");

        services.AddDbContext<AppDbContext>(options =>
        {
            if (string.IsNullOrWhiteSpace(connectionString))
            {
                options.UseInMemoryDatabase("QrCodeDb");
            }
            else
            {
                options.UseSqlServer(connectionString);
            }
        });

        services.AddScoped<IAuthService, AuthService>();
        services.AddScoped<ICategoryService, CategoryService>();
        services.AddScoped<IProductService, ProductService>();
        services.AddScoped<ICompanyInfoService, CompanyInfoService>();
        services.AddScoped<IJwtTokenService, JwtTokenService>();
        services.AddScoped<IFileStorageService>(_ => new FileStorageService(webRootPath));
        services.AddScoped<IDatabaseSeeder, DatabaseSeeder>();
        services.AddScoped<IQrCodeGenerator, QrCodeGeneratorService>();

        return services;
    }
}
