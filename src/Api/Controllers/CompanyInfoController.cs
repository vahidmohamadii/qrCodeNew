using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using QrCode.Application.Abstractions;
using QrCode.Application.Company;

namespace QrCode.Api.Controllers;

[ApiController]
[Route("api/company-info")]
public sealed class CompanyInfoController : ControllerBase
{
    private readonly ICompanyInfoService _companyInfoService;

    public CompanyInfoController(ICompanyInfoService companyInfoService)
    {
        _companyInfoService = companyInfoService;
    }

    [HttpGet]
    [AllowAnonymous]
    public async Task<ActionResult<CompanyInfoDto>> Get(CancellationToken cancellationToken)
    {
        return Ok(await _companyInfoService.GetAsync(cancellationToken));
    }

    [HttpPut]
    [Authorize(Roles = "Admin")]
    public async Task<ActionResult<CompanyInfoDto>> Update([FromBody] UpdateCompanyInfoRequest request, CancellationToken cancellationToken)
    {
        return Ok(await _companyInfoService.UpdateAsync(request, cancellationToken));
    }
}
