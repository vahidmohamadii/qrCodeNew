using QrCode.Application.Auth;
using QrCode.Application.Catalog;
using QrCode.Application.Company;

namespace QrCode.Application.Abstractions;

public interface IAuthService
{
    Task<AuthResponse?> LoginAsync(LoginRequest request, CancellationToken cancellationToken = default);

    Task<CurrentUserDto?> GetCurrentUserAsync(string email, CancellationToken cancellationToken = default);
}

public interface ICategoryService
{
    Task<IReadOnlyList<CategoryDto>> GetAllAsync(string? search = null, CancellationToken cancellationToken = default);

    Task<CategoryDto?> GetByIdAsync(int id, CancellationToken cancellationToken = default);

    Task<CategoryDto> CreateAsync(CategoryUpsertRequest request, CancellationToken cancellationToken = default);

    Task<CategoryDto?> UpdateAsync(int id, CategoryUpsertRequest request, CancellationToken cancellationToken = default);

    Task<bool> DeleteAsync(int id, CancellationToken cancellationToken = default);
}

public interface IProductService
{
    Task<IReadOnlyList<ProductDto>> GetAllAsync(string? search = null, int? categoryId = null, CancellationToken cancellationToken = default);

    Task<ProductDto?> GetByIdAsync(int id, CancellationToken cancellationToken = default);

    Task<PublicProductDto?> GetBySlugAsync(string slug, CancellationToken cancellationToken = default);

    Task<ProductDto> CreateAsync(ProductUpsertRequest request, CancellationToken cancellationToken = default);

    Task<ProductDto?> UpdateAsync(int id, ProductUpsertRequest request, CancellationToken cancellationToken = default);

    Task<bool> DeleteAsync(int id, CancellationToken cancellationToken = default);

    Task<ProductImageDto?> AddImageAsync(int productId, string imageUrl, string? altText, bool isMainImage, int sortOrder, CancellationToken cancellationToken = default);

    Task<bool> DeleteImageAsync(int imageId, CancellationToken cancellationToken = default);

    Task<ProductQrCodeDto?> CreateQrCodeAsync(int productId, QrCodeCreateRequest request, string publicBaseUrl, CancellationToken cancellationToken = default);
}

public interface ICompanyInfoService
{
    Task<CompanyInfoDto> GetAsync(CancellationToken cancellationToken = default);

    Task<CompanyInfoDto> UpdateAsync(UpdateCompanyInfoRequest request, CancellationToken cancellationToken = default);
}

public interface IFileStorageService
{
    Task<string> SaveImageAsync(Stream content, string fileName, string folderName, CancellationToken cancellationToken = default);
}

public interface IJwtTokenService
{
    string CreateToken(string email, string fullName, string role);
}
