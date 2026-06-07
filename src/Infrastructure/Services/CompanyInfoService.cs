using Microsoft.EntityFrameworkCore;
using QrCode.Application.Abstractions;
using QrCode.Application.Company;
using QrCode.Infrastructure.Data;

namespace QrCode.Infrastructure.Services;

internal sealed class CompanyInfoService : ICompanyInfoService
{
    private readonly AppDbContext _dbContext;

    public CompanyInfoService(AppDbContext dbContext)
    {
        _dbContext = dbContext;
    }

    public async Task<CompanyInfoDto> GetAsync(CancellationToken cancellationToken = default)
    {
        var entity = await _dbContext.CompanyInfos.AsNoTracking().FirstAsync(x => x.Id == 1, cancellationToken);
        return Map(entity);
    }

    public async Task<CompanyInfoDto> UpdateAsync(UpdateCompanyInfoRequest request, CancellationToken cancellationToken = default)
    {
        var entity = await _dbContext.CompanyInfos.FirstAsync(x => x.Id == 1, cancellationToken);
        entity.CompanyName = request.CompanyName;
        entity.Description = request.Description;
        entity.Mission = request.Mission;
        entity.Vision = request.Vision;
        entity.Services = request.Services;
        entity.ContactInformation = request.ContactInformation;
        entity.Address = request.Address;
        entity.Email = request.Email;
        entity.PhoneNumber = request.PhoneNumber;
        entity.SocialMediaLinks = request.SocialMediaLinks;

        await _dbContext.SaveChangesAsync(cancellationToken);
        return Map(entity);
    }

    private static CompanyInfoDto Map(Domain.Entities.CompanyInfo entity) => new()
    {
        CompanyName = entity.CompanyName,
        Description = entity.Description,
        Mission = entity.Mission,
        Vision = entity.Vision,
        Services = entity.Services,
        ContactInformation = entity.ContactInformation,
        Address = entity.Address,
        Email = entity.Email,
        PhoneNumber = entity.PhoneNumber,
        SocialMediaLinks = entity.SocialMediaLinks
    };
}
