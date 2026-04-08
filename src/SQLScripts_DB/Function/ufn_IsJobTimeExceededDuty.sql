USE [BBCSchedules]
GO
/****** Object:  UserDefinedFunction [dbo].[ufn_IsJobTimeExceededDuty]    Script Date: 10/07/2025 12:50:50 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER   FUNCTION [dbo].[ufn_IsJobTimeExceededDuty]
 (
	@AllocationsDutyID INT, 
	@DutyStartTime DATETIME, 
	@DutyEndTime DATETIME
 )
RETURNS INT
AS
BEGIN

	DECLARE @JobStartTime	 DATETIME,
			@JobEndTime		 DATETIME,
			@IsJobExceedTime BIT;

	SELECT @JobStartTime = MIN(AJ_JobStartTimeLocal),
		   @JobEndTime = MAX(AJ_JobEndTimeLocal)
	  FROM AllocationsJobs AJ 
	 WHERE AJ_AllocationsDutyID = @AllocationsDutyID

	SET @IsJobExceedTime = CASE WHEN ( @JobStartTime < @DutyStartTime OR @DutyEndTime < @JobEndTime)
								THEN 1
								ELSE 0
							END

	RETURN @IsJobExceedTime

END