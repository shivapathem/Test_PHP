USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_getSicknessCounts]    Script Date: 25/05/2023 17:12:43 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 23-95-2022
-- Description:	Used to get sickness details
-- =============================================
CREATE OR ALTER     PROCEDURE [dbo].[usp_getSicknessCounts] 
	@teamId INT,
	@scheduledPersonID VARCHAR(10),
	@startWeek INT,
	@endWeek INT,
	@startDay INT,
	@endDay INT
AS
BEGIN
	DECLARE @sDate Date
	DECLARE @eDate Date
	Select @sDate = dDateTime From TimeDimension where ixYearWeek = @startWeek AND ixDayInWeek = @startDay
	Select @eDate = dDateTime From TimeDimension where ixYearWeek = @endWeek AND ixDayInWeek = @endDay
	IF(@teamId > 0)
	BEGIN
	
		SELECT            AL.SchedulingPersonID ScheduledPersonID, SUM(CASE WHEN AL.Dutyname NOT IN('-Sick','Sick', 'U-Sick', 'Leave', 'OFF Leave', 'U') AND (AL.Duration <> 0)
							THEN 1 ELSE 0 END) AS CountWorkedDays, 0 AS HideStats
		FROM              Allocations AL
		INNER JOIn		  ScheduledPersonTeam_LINK SPTL ON SPTL.ScheduledPersonID = AL.SchedulingPersonID AND SPTL.TeamID = AL.SchedulingTeamId AND (SPTL.scheduledType = 1)
		INNER JOIN		  TimeDimension TD ON TD.ixYearWeek = AL.WeekNumber AND TD.ixDayInWeek = AL.iDay
		WHERE            (TD.dDateTime between @sDate AND @eDate) AND (SPTL.TeamID = @teamId) AND (SPTL.scheduledType = 1)
						  AND TD.dDateTime between SPTL.StartDate AND isNull(SPTL.EndDate, TD.dDateTime)
		GROUP BY          AL.SchedulingPersonID
	END
	ELSE
	BEGIN
		SELECT                 AL.StaffNumber, COUNT(AL.Duration) AS CountWorkedDays, UWC.HideStats
		FROM                   User_Web_Config UWC 
		RIGHT OUTER JOIN       StaffDetails SD ON UWC.Login = SD.NetLogin 
		RIGHT OUTER JOIN       Allocations AL ON UWC.SchedulingTeamId = AL.SchedulingTeamId AND SD.StaffNumber = AL.StaffNumber
		WHERE                  (AL.WeekNumber = @startWeek) AND (AL.iDay >= @endDay) AND (NOT (AL.DutyName LIKE N'%leave%')) AND (NOT (AL.DutyName LIKE N'%sick%')) AND (AL.Duration <> 0) 
		OR                     (AL.WeekNumber = @endWeek) AND (AL.iDay <= @endDay) AND (NOT (AL.DutyName LIKE N'%leave%')) AND (NOT (AL.DutyName LIKE N'%sick%')) AND (AL.Duration <> 0) 
		OR                     (AL.WeekNumber > @startWeek) AND (NOT (AL.DutyName LIKE N'%leave%')) AND (NOT (AL.DutyName LIKE N'%sick%')) AND (AL.Duration <> 0) AND (AL.WeekNumber < @endWeek)
		GROUP BY               AL.StaffNumber, UWC.HideStats
		HAVING                 (AL.StaffNumber = @scheduledPersonID)
	END
END