USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_Remove_AdHocDuties]    Script Date: 05/03/2025 17:58:27 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER       PROCEDURE [dbo].[usp_Remove_AdHocDuties]
@DutyDate         VARCHAR(30),
@schedulingTeamID      INT,
@AdhocID               INT,
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
			where schedulingteamid = @schedulingTeamID 
			  and dutydate = CONVERT(DATETIME,@DutyDate,101) 
			  and OrigAllocationID = @AdhocID
			  and adhocduty = 1

        IF ( @@ROWCOUNT > 0 )
		 BEGIN
			
           delete Allocations_publish
		    where AllocationID = ( SELECT ID 
			                         FROM Allocations 
									where schedulingteamid = @schedulingTeamID 
									  and dutydate = CONVERT(DATETIME,@DutyDate,101) 
									  and OrigAllocationID = @AdhocID 
									  and adhocduty = 1)
    

           delete Allocations 
			where schedulingteamid = @schedulingTeamID 
			  and dutydate = CONVERT(DATETIME,@DutyDate,101) 
			  and OrigAllocationID = @AdhocID  
			  and adhocduty = 1

	     END

		   delete adhoc_duty
		    where AdhocID = @AdhocID
		
 END