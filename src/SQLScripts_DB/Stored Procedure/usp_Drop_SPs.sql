
USE [Teampay_Aux]
IF OBJECT_ID('dbo.usp_EXPORT_A7_AllocMasterJobs', 'P') IS NOT NULL
BEGIN
    DROP PROCEDURE dbo.usp_EXPORT_A7_AllocMasterJobs;
END
IF OBJECT_ID('dbo.usp_EXPORT_A7_AllocMiscDuties', 'P') IS NOT NULL
BEGIN
    DROP PROCEDURE dbo.usp_EXPORT_A7_AllocMiscDuties;
END
IF OBJECT_ID('dbo.usp_A7_Link_MasterDuty_MasterJob', 'P') IS NOT NULL
BEGIN
    DROP PROCEDURE dbo.usp_A7_Link_MasterDuty_MasterJob;
END