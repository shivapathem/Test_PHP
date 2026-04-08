USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_ReadMasterDuties]    Script Date: 13/03/2024 12:04:07 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- =============================================
-- Author:		HCL
-- Create date: 08-05-2022
-- Description:	used to get master duty details by team
-- =============================================

CREATE OR ALTER PROCEDURE [dbo].[usp_ReadMasterDuties] 
	@teamId INT,
	@startWeekNumber INT,
	@endWeekNumber INT
AS
BEGIN

	SET NOCOUNT ON;

	SELECT DepartmentID, 
	       AllocMasterDutyID, 
		   DutyName, 
		   WeekNumber, 
		   StartTime, 
		   EndTime, 
		   DutyDuration, 
		   EFT,
		   TeamDesc, 
		   BaseCode, 
		   BaseName, 
		   dutyBreakTime BreakTime, 
		   DutyTypeID, 	  
		   max(sat) [0], 
		   max(sun) [1],
		   max(mon) [2],
		   max(tue) [3],
		   max(wed) [4],
		   max(thu) [5], 
		   max(fri) [6], 
		   (StartTime/60) StartMinutes, 
		   (EndTime/60) EndMinutes
	 FROM
	     (   SELECT MD.TeamID DepartmentID, 
					MD.MasterDutyId AllocMasterDutyID, 
					MD.DutyName, 
					TD.ixYearWeek WeekNumber, 
					MD.StartTime, 
					MD.EndTime,
					CASE WHEN ISNULL(md.duration,0) = 0 
					     THEN 
							 CASE WHEN md.endtime > md.starttime 
								  THEN md.endtime- md.starttime
								  WHEN md.endtime < md.starttime 
								  THEN (86400-md.starttime)+md.endtime 
							  END
						 ELSE md.duration END DutyDuration, 
					0 EFT, 
					'' AS TeamDesc, 
					0 BaseCode, 
					'' AS BaseName, 
					MD.BreakTime dutyBreakTime,
					MD.DutyTypeID  DutyTypeID, 
					CASE WHEN MD.Saturday > 0 THEN MD.Saturday ELSE 0 END AS sat,
					CASE WHEN MD.Sunday > 0 THEN MD.Sunday ELSE 0 END AS sun,
					CASE WHEN MD.Monday > 0 THEN MD.Monday ELSE 0 END AS mon,
					CASE WHEN MD.Tuesday > 0 THEN MD.Tuesday ELSE 0 END AS tue,
					CASE WHEN MD.Wednesday > 0 THEN MD.Wednesday ELSE 0 END AS wed,
					CASE WHEN MD.Thursday > 0 THEN MD.Thursday ELSE 0 END AS thu,
					CASE WHEN MD.Friday > 0 THEN MD.Friday ELSE 0 END AS fri
			  FROM  MasterDuties MD 
			  INNER JOIN TimeDimension TD ON TD.ixYearWeek BETWEEN MD.StartWeek AND MD.EndWeek
              WHERE MD.DutyTypeID = 1 
				AND MD.IsActive = 1
			    AND MD.TeamID = @teamId 
			    AND TD.ixYearWeek BETWEEN @startWeekNumber AND @endWeekNumber
			    AND NOT EXISTS ( SELECT 1 
			                       FROM MasterDutiesHide MDH 
								  WHERE MDH.AllocMasterDutyID = MD.MasterDutyId 
			                        AND MDH.DepartmentID = MD.TeamID 
								    AND MDH.isHidden = 1
								)
			 )  AS AL
			 GROUP BY DepartmentID, 
			          AllocMasterDutyID, 
					  DutyName, 
					  WeekNumber, 
					  StartTime, 
					  EndTime, 
					  DutyDuration, 
					  EFT, 
					  TeamDesc, 
					  BaseCode, 
					  BaseName, 
					  dutyBreakTime, 
					  DutyTypeID
             ORDER BY DutyName
			 
END
