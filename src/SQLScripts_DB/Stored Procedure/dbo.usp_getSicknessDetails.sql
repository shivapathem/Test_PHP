USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_getSicknessDetails]    Script Date: 25/05/2023 17:16:18 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 23-95-2022
-- Description:	Used to get sickness details
-- =============================================
CREATE OR ALTER   PROCEDURE [dbo].[usp_getSicknessDetails] 
	@teamId INT,
	@scheduledPersonID INT,
	@startWeek INT,
	@endWeek INT,
	@startDay INT,
	@endDay INT
AS
BEGIN
	DECLARE @queryStr VARCHAR(MAX) = ''
	DECLARE @sDate Date
	DECLARE @eDate Date
	Select @sDate = dDateTime From TimeDimension where ixYearWeek = @startWeek AND ixDayInWeek = @startDay
	Select @eDate = dDateTime From TimeDimension where ixYearWeek = @endWeek AND ixDayInWeek = @endDay
	SET @queryStr = 'SELECT SP.DisplayName FullName, SPTL.SortCode, SP.ScheduledPersonID, AL.WeekNumber, AL.iDay, 
		cast(ALS.Hours as float) / cast(3600 as float) Duration, ISNULL(UWC.HideStats, 0) AS HideStats
		FROM Allocations AL
		INNER JOIN TimeDimension TD On TD.ixYearWeek = AL.WeekNumber AND TD.ixDayInWeek = AL.iDay
		INNER JOIN ScheduledPeople SP ON SP.ScheduledPersonID = AL.SchedulingPersonID
		INNER JOIN ScheduledPersonTeam_LINK SPTL ON SPTL.ScheduledPersonID = SP.ScheduledPersonID 
		                                        AND AL.SchedulingTeamId = SPTL.TeamID AND (SPTL.scheduledType = 1)
		INNER JOIN AllocationSickness ALS ON AL.ID = ALS.AllocationID 
		LEFT JOIN Users USR ON USR.UserId = SP.UserId
		LEFT JOIN User_Web_Config UWC ON UWC.Login = USR.NetLogin AND UWC.SchedulingTeamId = SPTL.TeamID		
		WHERE (TD.dDateTime between ' + '''' + CAST(@sDate AS VARCHAR) + '''' + ' AND ' + '''' + CAST(@eDate AS VARCHAR) + '''' + ') 
		AND (TD.dDateTime between SPTL.StartDate AND isNull(SPTL.EndDate, TD.dDateTime)) AND (Al.DutyName in(''Sick'', ''U-Sick'',''-Sick''))'
		IF(@teamId > 0)
		BEGIN
			SET @queryStr = @queryStr + ' AND (SPTL.TeamID = ' + CAST(@teamId AS VARCHAR) + ')'
		END
		IF(@scheduledPersonID > 0)
		BEGIN
			SET @queryStr = @queryStr + ' AND (SP.ScheduledPersonID = ' + CAST(@scheduledPersonID AS VARCHAR) + ')'
		END
		SET @queryStr = @queryStr + '  ORDER BY AL.WeekNumber, AL.iDay'

		exec(@queryStr)
END