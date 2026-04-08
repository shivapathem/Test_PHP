USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ReadUnassignAllocationsEditWeekly]    Script Date: 08/04/2022 19:11:48 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE  [dbo].[usp_get_ReadUnassignAllocationsEditWeekly]
@startDate VARCHAR(20),
@endDate VARCHAR(20),
@schedulingTeamId VARCHAR(5),
@mastMiscFilterId VARCHAR(5),
@orderByCond VARCHAR(255)
AS
BEGIN

	SET NOCOUNT ON
	DECLARE @sql VARCHAR(MAX),
		    @sqlCond VARCHAR(MAX)

	SET @endDate = CONVERT(DATETIME,@endDate,101)  - 1

	IF(@orderByCond = '')
		SET @orderByCond = ' ORDER BY a.DutyName'

	SET @sqlCond = ''
	
	IF(@mastMiscFilterId > 0)
		SET @sqlCond = ' AND a.DutyName IN(SELECT DISTINCT md.DutyName FROM MasterDutiesFilterLinks mdfl
                      JOIN MasterDuties md ON md.MasterDutyID = mdfl.MasterDutyID
                      WHERE mdfl.FilterID='+@mastMiscFilterId+') '

		SET @sql =  'SELECT a.AllocateInstanceID,
							a.DepartmentID,
							AllocationID,
							StaffNumber,
							DutyName,
							Duration,
							WeekNumber,
							iDay,
							StartTime,
							EndTime,
							ActingGrade,
							SortCode,
							LeaveID,
							ManualERR,
							DutyComments,
							BaseCode,
							BackColour,
							FontColour,
							PersonComments,
							AdhocDuty,
							MarkedOvertime,
							MarkedPTExtraDay,
							MarkedCompLeave,
							MarkedSickness,
							ManualOTAmount,
							ManualOTExcBreaksAmount,
							UnAllocated,
							A.ID,
							SchedulingTeamId,
							SchedulingPersonID,
							StartDate,
							EndDate,
							isPublished,
							IsHomeTeam,
							MarkWiad,
							MarkActual,
							aftermidnight,
							isAttention,
							isRequest,
							dutyProgramId,
							dutyBreakTime,
							dutyColorId,
							MannualOThours,
							isEdited,
							MasterDutyId,
							isActive,
							isActiveDuty,
							isEditable,
							OrigAllocationID,
							MarkWTD,
							WTDComments,
							isCompareEdited,
							DutyTeamID,
		                    FORMAT (a.DutyDate, ''yyyy-MM-dd'') as DutyDate, 
							a.DutyDate as DutyDateTime,
							count(a.DutyName) over ( partition by a.dutydate, dutyname ) AS DutyInstances
					   FROM dbo.Allocations a (NOLOCK)
					  INNER JOIN TimeDimension TD ON a.WeekNumber = td.ixYearWeek AND a.iday = td.ixDayInWeek
					  WHERE ISNULL(a.SchedulingPersonID,0) = 0
						AND a.SchedulingTeamId = '+@schedulingTeamId+
						' AND td.dDateTime BETWEEN CONVERT(DATETIME, '''+@startDate+''', 101)
						                     AND CONVERT(DATETIME, '''+@endDate+''', 101)
						AND a.DutyName is not null '+@sqlCond+@orderByCond
	exec (@sql)
END
