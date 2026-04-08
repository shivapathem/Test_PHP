USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_Remove_Allocations]    Script Date: 10/07/2023 14:07:25 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER     PROCEDURE [dbo].[usp_Remove_Allocations]
@DutyStartDate         VARCHAR(30),
@schedulingTeamID      INT,
@schedulingPersonID    INT,
@pNetLogin             VARCHAR(30)
AS
BEGIN

	SET NOCOUNT ON;

	DECLARE  @vuserID        INT 

   select @vuserID = UserID 
     from Users  (NOLOCK)
    where NetLogin = @pNetLogin  

			INSERT INTO Allocations_Removed
				   (AllocateInstanceID
				   ,DepartmentID
				   ,AllocationID
				   ,StaffNumber
				   ,DutyName
				   ,Duration
				   ,WeekNumber
				   ,iDay
				   ,StartTime
				   ,EndTime
				   ,ActingGrade
				   ,SortCode
				   ,LeaveID
				   ,ManualERR
				   ,DutyComments
				   ,BaseCode
				   ,BackColour
				   ,FontColour
				   ,PersonComments
				   ,AdhocDuty
				   ,MarkedOvertime
				   ,MarkedPTExtraDay
				   ,MarkedCompLeave
				   ,MarkedSickness
				   ,ManualOTAmount
				   ,ManualOTExcBreaksAmount
				   ,UnAllocated
				   ,ID
				   ,SchedulingTeamId
				   ,SchedulingPersonID
				   ,DutyDate
				   ,StartDate
				   ,EndDate
				   ,isPublished
				   ,IsHomeTeam
				   ,MarkWiad
				   ,MarkActual
				   ,aftermidnight
				   ,isAttention
				   ,isRequest
				   ,dutyProgramId
				   ,dutyBreakTime
				   ,dutyColorId
				   ,MannualOThours
				   ,isEdited
				   ,MasterDutyId
				   ,isActive
				   ,isActiveDuty
				   ,isEditable
				   ,OrigAllocationID
				   ,MarkWTD
				   ,WTDComments
				   ,isCompareEdited
				   ,DutyTeamID
				   ,PlannedDuration
				   ,MarkOverTwelve
				   ,OverTwelveHrs
				   ,IsOverseasOverTwelve
				   ,IsUnderElevenBreak
				   ,CalculatedUnderElevenHrs
				   ,IsUnderElevenBreakOverride
				   ,OverrideUnderElevenHrs
				   ,UnderElevenComment
				   ,CreatedBy
				   ,CreatedDate
				   ,UpdatedBy
				   ,UpdatedDate)
			SELECT AllocateInstanceID
				   ,DepartmentID
				   ,AllocationID
				   ,StaffNumber
				   ,DutyName
				   ,Duration
				   ,WeekNumber
				   ,iDay
				   ,StartTime
				   ,EndTime
				   ,ActingGrade
				   ,SortCode
				   ,LeaveID
				   ,ManualERR
				   ,DutyComments
				   ,BaseCode
				   ,BackColour
				   ,FontColour
				   ,PersonComments
				   ,AdhocDuty
				   ,MarkedOvertime
				   ,MarkedPTExtraDay
				   ,MarkedCompLeave
				   ,MarkedSickness
				   ,ManualOTAmount
				   ,ManualOTExcBreaksAmount
				   ,UnAllocated
				   ,ID
				   ,SchedulingTeamId
				   ,SchedulingPersonID
				   ,DutyDate
				   ,StartDate
				   ,EndDate
				   ,isPublished
				   ,IsHomeTeam
				   ,MarkWiad
				   ,MarkActual
				   ,aftermidnight
				   ,isAttention
				   ,isRequest
				   ,dutyProgramId
				   ,dutyBreakTime
				   ,dutyColorId
				   ,MannualOThours
				   ,isEdited
				   ,MasterDutyId
				   ,isActive
				   ,isActiveDuty
				   ,isEditable
				   ,OrigAllocationID
				   ,MarkWTD
				   ,WTDComments
				   ,isCompareEdited
				   ,DutyTeamID
				   ,PlannedDuration
				   ,MarkOverTwelve
				   ,OverTwelveHrs
				   ,IsOverseasOverTwelve
				   ,IsUnderElevenBreak
				   ,CalculatedUnderElevenHrs
				   ,IsUnderElevenBreakOverride
				   ,OverrideUnderElevenHrs
				   ,UnderElevenComment
				   ,CreatedBy
				   ,CreatedDate
				   ,@vuserID
				   ,getutcdate()
			 FROM Allocations 
			where schedulingpersonid = @schedulingPersonID 
			  and schedulingteamid = @schedulingTeamID 
			  and dutydate > CONVERT(DATETIME,@DutyStartDate,101)  

			
           delete Allocations_publish 
			where schedulingpersonid = @schedulingPersonID 
			  and schedulingteamid = @schedulingTeamID 
			  and dutydate > CONVERT(DATETIME,@DutyStartDate,101)   

           delete Allocations 
			where schedulingpersonid = @schedulingPersonID 
			  and schedulingteamid = @schedulingTeamID 
			  and dutydate > CONVERT(DATETIME,@DutyStartDate,101)  
		
 END