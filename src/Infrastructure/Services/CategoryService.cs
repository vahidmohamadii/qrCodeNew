using Microsoft.EntityFrameworkCore;
using QrCode.Application.Abstractions;
using QrCode.Application.Catalog;
using QrCode.Infrastructure.Data;

namespace QrCode.Infrastructure.Services;

internal sealed class CategoryService : ICategoryService
{
    private readonly AppDbContext _dbContext;

    public CategoryService(AppDbContext dbContext)
    {
        _dbContext = dbContext;
    }

    public async Task<IReadOnlyList<CategoryDto>> GetAllAsync(string? search = null, CancellationToken cancellationToken = default)
    {
        var query = _dbContext.Categories.AsNoTracking().AsQueryable();
        if (!string.IsNullOrWhiteSpace(search))
        {
            query = query.Where(x => x.Name.Contains(search) || x.Slug.Contains(search));
        }

        return await query
            .OrderBy(x => x.Name)
            .Select(x => new CategoryDto
            {
                Id = x.Id,
                Name = x.Name,
                Slug = x.Slug,
                Description = x.Description,
                ParentCategoryId = x.ParentCategoryId,
                ImageUrl = x.ImageUrl,
                IsActive = x.IsActive
            })
            .ToListAsync(cancellationToken);
    }

    public async Task<CategoryDto?> GetByIdAsync(int id, CancellationToken cancellationToken = default)
    {
        var category = await _dbContext.Categories.AsNoTracking().FirstOrDefaultAsync(x => x.Id == id, cancellationToken);
        return category is null ? null : Map(category);
    }

    public async Task<CategoryDto> CreateAsync(CategoryUpsertRequest request, CancellationToken cancellationToken = default)
    {
        var entity = new Domain.Entities.Category
        {
            Name = request.Name,
            Slug = SecurityHelpers.Slugify(request.Name),
            Description = request.Description,
            ParentCategoryId = request.ParentCategoryId,
            ImageUrl = request.ImageUrl,
            IsActive = request.IsActive
        };

        _dbContext.Categories.Add(entity);
        await _dbContext.SaveChangesAsync(cancellationToken);
        return Map(entity);
    }

    public async Task<CategoryDto?> UpdateAsync(int id, CategoryUpsertRequest request, CancellationToken cancellationToken = default)
    {
        var entity = await _dbContext.Categories.FirstOrDefaultAsync(x => x.Id == id, cancellationToken);
        if (entity is null)
        {
            return null;
        }

        entity.Name = request.Name;
        entity.Slug = SecurityHelpers.Slugify(request.Name);
        entity.Description = request.Description;
        entity.ParentCategoryId = request.ParentCategoryId;
        entity.ImageUrl = request.ImageUrl;
        entity.IsActive = request.IsActive;

        await _dbContext.SaveChangesAsync(cancellationToken);
        return Map(entity);
    }

    public async Task<bool> DeleteAsync(int id, CancellationToken cancellationToken = default)
    {
        var entity = await _dbContext.Categories.FirstOrDefaultAsync(x => x.Id == id, cancellationToken);
        if (entity is null)
        {
            return false;
        }

        _dbContext.Categories.Remove(entity);
        await _dbContext.SaveChangesAsync(cancellationToken);
        return true;
    }

    private static CategoryDto Map(Domain.Entities.Category entity) => new()
    {
        Id = entity.Id,
        Name = entity.Name,
        Slug = entity.Slug,
        Description = entity.Description,
        ParentCategoryId = entity.ParentCategoryId,
        ImageUrl = entity.ImageUrl,
        IsActive = entity.IsActive
    };
}
