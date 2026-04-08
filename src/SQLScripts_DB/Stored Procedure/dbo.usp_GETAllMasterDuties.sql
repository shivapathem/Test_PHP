USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GETAllMasterDuties]    Script Date: 13/11/2025 17:51:05 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 10-05-2022
-- Description:	used to return all master duties by team id
-- =============================================
CREATE OR ALTER     PROCEDURE [dbo].[usp_GETAllMasterDuties] 
	@teamId INT,
	@weekNumber VARCHAR(10)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    SELECT   MasterDuties.TeamID DepartmentID, MasterDuties.MasterDutyID AllocMasterDutyID, MasterDuties.DutyName, 
                MasterDuties.StartWeek, MasterDuties.EndWeek, MasterDuties.StartTime, 
                MasterDuties.EndTime, MasterDuties.StartTime StartMinutes, MasterDuties.EndTime EndMinutes, 
                MasterDuties.Duration DutyDuration, MasterDuties.Saturday AS [0], MasterDuties.Sunday AS [1], 
                MasterDuties.Monday AS [2], MasterDuties.Tuesday AS [3], MasterDuties.Wednesday AS [4], 
                MasterDuties.Thursday AS [5], MasterDuties.Friday AS [6], MasterDuties.IsActive, 
                MasterDuties.LastModBy LastModDateTime, MasterDuties.CreatedDate InsertDateTime, MasterDuties.History, 
                '' AS TeamDesc, '' BaseCode, 0 AS isHidden, '' BaseName, isNull(MDH.isHidden, 0) isHidden
	FROM      MasterDuties WITH (NOLOCK)
	LEFT JOIN MasterDutiesHide MDH ON MDH.AllocMasterDutyID = MasterDutyID AND MDH.DepartmentID = TeamID
	WHERE   (MasterDuties.TeamID = @teamId) AND MasterDuties.EndWeek >= @weekNumber and MasterDuties.DutyTypeID not in (6)
	ORDER BY MasterDuties.DutyName

END