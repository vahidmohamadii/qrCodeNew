using Microsoft.EntityFrameworkCore;
using QrCode.Application.Abstractions;
using QrCode.Application.Catalog;
using QrCode.Infrastructure.Data;

namespace QrCode.Infrastructure.Services;

internal interface IQrCodeGenerator
{
    byte[] GeneratePng(string payload);
}

internal sealed class QrCodeGeneratorService : IQrCodeGenerator
{
    public byte[] GeneratePng(string payload)
    {
        using var generator = new QRCoder.QRCodeGenerator();
        using var data = generator.CreateQrCode(payload, QRCoder.QRCodeGenerator.ECCLevel.Q);
        var png = new QRCoder.PngByteQRCode(data);
        return png.GetGraphic(20);
    }
}

internal sealed class ProductService : IProductService
{
    private readonly AppDbContext _dbContext;
    private readonly IFileStorageService _fileStorageService;
    private readonly IQrCodeGenerator _qrCodeGenerator;

    public ProductService(AppDbContext dbContext, IFileStorageService fileStorageService, IQrCodeGenerator qrCodeGenerator)
    {
        _dbContext = dbContext;
        _fileStorageService = fileStorageService;
        _qrCodeGenerator = qrCodeGenerator;
    }

    public async Task<IReadOnlyList<ProductDto>> GetAllAsync(string? search = null, int? categoryId = null, CancellationToken cancellationToken = default)
    {
        var query = _dbContext.Products
            .AsNoTracking()
            .Include(x => x.Category)
            .Include(x => x.Images)
            .AsQueryable();

        if (!string.IsNullOrWhiteSpace(search))
        {
            query = query.Where(x => x.Name.Contains(search) || x.SKU.Contains(search) || x.ProductCode.Contains(search));
        }

        if (categoryId.HasValue)
        {
            query = query.Where(x => x.CategoryId == categoryId.Value);
        }

        return await query
            .OrderBy(x => x.Name)
            .Select(x => Map(x))
            .ToListAsync(cancellationToken);
    }

    public async Task<ProductDto?> GetByIdAsync(int id, CancellationToken cancellationToken = default)
    {
        var product = await _dbContext.Products
            .AsNoTracking()
            .Include(x => x.Category)
            .Include(x => x.Images)
            .FirstOrDefaultAsync(x => x.Id == id, cancellationToken);

        return product is null ? null : Map(product);
    }

    public async Task<PublicProductDto?> GetBySlugAsync(string slug, CancellationToken cancellationToken = default)
    {
        var product = await _dbContext.Products
            .AsNoTracking()
            .Include(x => x.Category)
            .Include(x => x.Images)
            .FirstOrDefaultAsync(x => x.Slug == slug && x.IsActive, cancellationToken);

        if (product is null)
        {
            return null;
        }

        return new PublicProductDto
        {
            Name = product.Name,
            Slug = product.Slug,
            CategoryName = product.Category?.Name ?? string.Empty,
            SKU = product.SKU,
            ProductCode = product.ProductCode,
            ShortDescription = product.ShortDescription,
            FullDescription = product.FullDescription,
            Brand = product.Brand,
            Model = product.Model,
            Price = product.Price,
            Currency = product.Currency,
            WarrantyInfo = product.WarrantyInfo,
            TechnicalSpecifications = product.TechnicalSpecifications,
            AdditionalInformation = product.AdditionalInformation,
            Images = product.Images
                .OrderBy(x => x.SortOrder)
                .Select(x => new ProductImageDto
                {
                    Id = x.Id,
                    ProductId = x.ProductId,
                    ImageUrl = x.ImageUrl,
                    AltText = x.AltText,
                    SortOrder = x.SortOrder,
                    IsMainImage = x.IsMainImage
                })
                .ToList()
        };
    }

    public async Task<ProductDto> CreateAsync(ProductUpsertRequest request, CancellationToken cancellationToken = default)
    {
        var entity = new Domain.Entities.Product
        {
            CategoryId = request.CategoryId,
            Name = request.Name,
            Slug = string.IsNullOrWhiteSpace(request.Slug) ? SecurityHelpers.Slugify(request.Name) : SecurityHelpers.Slugify(request.Slug),
            SKU = request.SKU,
            ShortDescription = request.ShortDescription,
            FullDescription = request.FullDescription,
            Brand = request.Brand,
            Model = request.Model,
            Price = request.Price,
            Currency = request.Currency,
            StockQuantity = request.StockQuantity,
            ProductCode = request.ProductCode,
            Barcode = request.Barcode,
            Weight = request.Weight,
            Dimensions = request.Dimensions,
            Material = request.Material,
            Color = request.Color,
            WarrantyInfo = request.WarrantyInfo,
            TechnicalSpecifications = request.TechnicalSpecifications,
            AdditionalInformation = request.AdditionalInformation,
            MetaTitle = request.MetaTitle,
            MetaDescription = request.MetaDescription,
            IsActive = request.IsActive,
            IsFeatured = request.IsFeatured
        };

        _dbContext.Products.Add(entity);
        await _dbContext.SaveChangesAsync(cancellationToken);
        return (await GetByIdAsync(entity.Id, cancellationToken))!;
    }

    public async Task<ProductDto?> UpdateAsync(int id, ProductUpsertRequest request, CancellationToken cancellationToken = default)
    {
        var entity = await _dbContext.Products.FirstOrDefaultAsync(x => x.Id == id, cancellationToken);
        if (entity is null)
        {
            return null;
        }

        entity.CategoryId = request.CategoryId;
        entity.Name = request.Name;
        entity.Slug = string.IsNullOrWhiteSpace(request.Slug) ? SecurityHelpers.Slugify(request.Name) : SecurityHelpers.Slugify(request.Slug);
        entity.SKU = request.SKU;
        entity.ShortDescription = request.ShortDescription;
        entity.FullDescription = request.FullDescription;
        entity.Brand = request.Brand;
        entity.Model = request.Model;
        entity.Price = request.Price;
        entity.Currency = request.Currency;
        entity.StockQuantity = request.StockQuantity;
        entity.ProductCode = request.ProductCode;
        entity.Barcode = request.Barcode;
        entity.Weight = request.Weight;
        entity.Dimensions = request.Dimensions;
        entity.Material = request.Material;
        entity.Color = request.Color;
        entity.WarrantyInfo = request.WarrantyInfo;
        entity.TechnicalSpecifications = request.TechnicalSpecifications;
        entity.AdditionalInformation = request.AdditionalInformation;
        entity.MetaTitle = request.MetaTitle;
        entity.MetaDescription = request.MetaDescription;
        entity.IsActive = request.IsActive;
        entity.IsFeatured = request.IsFeatured;

        await _dbContext.SaveChangesAsync(cancellationToken);
        return (await GetByIdAsync(entity.Id, cancellationToken))!;
    }

    public async Task<bool> DeleteAsync(int id, CancellationToken cancellationToken = default)
    {
        var entity = await _dbContext.Products.FirstOrDefaultAsync(x => x.Id == id, cancellationToken);
        if (entity is null)
        {
            return false;
        }

        _dbContext.Products.Remove(entity);
        await _dbContext.SaveChangesAsync(cancellationToken);
        return true;
    }

    public async Task<ProductImageDto?> AddImageAsync(int productId, string imageUrl, string? altText, bool isMainImage, int sortOrder, CancellationToken cancellationToken = default)
    {
        var product = await _dbContext.Products.Include(x => x.Images).FirstOrDefaultAsync(x => x.Id == productId, cancellationToken);
        if (product is null)
        {
            return null;
        }

        if (isMainImage)
        {
            foreach (var image in product.Images)
            {
                image.IsMainImage = false;
            }
        }

        var imageEntity = new Domain.Entities.ProductImage
        {
            ProductId = productId,
            ImageUrl = imageUrl,
            AltText = altText,
            IsMainImage = isMainImage,
            SortOrder = sortOrder
        };

        _dbContext.ProductImages.Add(imageEntity);
        await _dbContext.SaveChangesAsync(cancellationToken);

        return new ProductImageDto
        {
            Id = imageEntity.Id,
            ProductId = productId,
            ImageUrl = imageUrl,
            AltText = altText,
            IsMainImage = isMainImage,
            SortOrder = sortOrder
        };
    }

    public async Task<bool> DeleteImageAsync(int imageId, CancellationToken cancellationToken = default)
    {
        var entity = await _dbContext.ProductImages.FirstOrDefaultAsync(x => x.Id == imageId, cancellationToken);
        if (entity is null)
        {
            return false;
        }

        _dbContext.ProductImages.Remove(entity);
        await _dbContext.SaveChangesAsync(cancellationToken);
        return true;
    }

    public async Task<ProductQrCodeDto?> CreateQrCodeAsync(int productId, QrCodeCreateRequest request, string publicBaseUrl, CancellationToken cancellationToken = default)
    {
        var product = await _dbContext.Products.FirstOrDefaultAsync(x => x.Id == productId, cancellationToken);
        if (product is null)
        {
            return null;
        }

        var qrUrl = $"{publicBaseUrl.TrimEnd('/')}/products/{product.Slug}";
        var png = _qrCodeGenerator.GeneratePng(qrUrl);
        var fileName = $"{product.Slug}-qr.png";
        var imagePath = await _fileStorageService.SaveImageAsync(new MemoryStream(png), fileName, "qr-codes", cancellationToken);

        var entity = new Domain.Entities.ProductQrCode
        {
            ProductId = productId,
            QrCodeUrl = qrUrl,
            QrCodeImagePath = imagePath,
            LabelTitle = request.LabelTitle,
            LabelDescription = request.LabelDescription,
            BatchNumber = request.BatchNumber,
            SerialNumber = request.SerialNumber,
            ManufacturingDate = request.ManufacturingDate,
            ExpiryDate = request.ExpiryDate,
            CustomNote = request.CustomNote
        };

        _dbContext.ProductQrCodes.Add(entity);
        await _dbContext.SaveChangesAsync(cancellationToken);

        return new ProductQrCodeDto
        {
            Id = entity.Id,
            ProductId = entity.ProductId,
            QrCodeUrl = entity.QrCodeUrl,
            QrCodeImagePath = entity.QrCodeImagePath,
            LabelTitle = entity.LabelTitle,
            LabelDescription = entity.LabelDescription,
            BatchNumber = entity.BatchNumber,
            SerialNumber = entity.SerialNumber,
            ManufacturingDate = entity.ManufacturingDate,
            ExpiryDate = entity.ExpiryDate,
            CustomNote = entity.CustomNote
        };
    }

    private static ProductDto Map(Domain.Entities.Product entity) => new()
    {
        Id = entity.Id,
        CategoryId = entity.CategoryId,
        CategoryName = entity.Category?.Name ?? string.Empty,
        Name = entity.Name,
        Slug = entity.Slug,
        SKU = entity.SKU,
        ShortDescription = entity.ShortDescription,
        FullDescription = entity.FullDescription,
        Brand = entity.Brand,
        Model = entity.Model,
        Price = entity.Price,
        Currency = entity.Currency,
        StockQuantity = entity.StockQuantity,
        ProductCode = entity.ProductCode,
        Barcode = entity.Barcode,
        Weight = entity.Weight,
        Dimensions = entity.Dimensions,
        Material = entity.Material,
        Color = entity.Color,
        WarrantyInfo = entity.WarrantyInfo,
        TechnicalSpecifications = entity.TechnicalSpecifications,
        AdditionalInformation = entity.AdditionalInformation,
        MetaTitle = entity.MetaTitle,
        MetaDescription = entity.MetaDescription,
        IsActive = entity.IsActive,
        IsFeatured = entity.IsFeatured,
        Images = entity.Images
            .OrderBy(x => x.SortOrder)
            .Select(x => new ProductImageDto
            {
                Id = x.Id,
                ProductId = x.ProductId,
                ImageUrl = x.ImageUrl,
                AltText = x.AltText,
                SortOrder = x.SortOrder,
                IsMainImage = x.IsMainImage
            })
            .ToList()
    };
}
