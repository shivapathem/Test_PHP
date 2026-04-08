USE [BBCSchedules]
GO
/****** Object:  UserDefinedFunction [dbo].[ufn_check_DutyOverLap_NextDay]    Script Date: 10/07/2025 12:48:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER   FUNCTION [dbo].[ufn_check_DutyOverLap_NextDay]
(
	@AllocationsSPID	INT,
	@DutyEndTime		DATETIME
)
RETURNS @StatusMessage  TABLE  
            ( 
			   IsDutyOverLap	  BIT,
			   NextDayOvrlapMsg     NVARCHAR(500)
			 ) 
AS
BEGIN

		DECLARE @PrevDayOvrlapMsg		NVARCHAR(500),
			    @NextDayOvrlapMsg		NVARCHAR(500),
				@SPDisplayName			VARCHAR(100),
				@OverLapStatus			BIT = 0,
				@PrevEndTime			DATETIME,
				@NextStartTime			DATETIME,
				@UserDisplayName		NVARCHAR(100);

		SELECT 
			@NextStartTime = ( SELECT CASE WHEN ISNULL(NT.AD_DutyStartTimeLocal,@DutyEndTime) < @DutyEndTime
											 THEN NT.AD_DutyStartTimeLocal 
											 ELSE NULL END AS NextStartTime
				FROM AllocationsDuties NT (nolock)
			   INNER JOIN AllocationsScheduledPersons NTS on NTS.ASP_AllocationsDutyID = NT.AD_AllocationsDutyID
			   WHERE NTS.ASP_SchedulingPersonID = ASP.ASP_SchedulingPersonID
				 AND NTS.ASP_DutyDate = DATEADD(DAY,1,AD.AD_DutyDate)
				 AND NT.AD_DutyType NOT IN (7,8,9)
			),
			@UserDisplayName = UD_DisplayName
		FROM AllocationsDuties AD (nolock)
	   INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsDutyID = AD.AD_AllocationsDutyID
	   INNER JOIN UserDetails UD on UD.UD_USerID = ASP.ASP_SchedulingPersonID
	   WHERE ASP.ASP_AllocationsSPID = @AllocationsSPID


				IF ( @NextStartTime IS NOT NULL )
				 BEGIN
				   SET @OverLapStatus = 1
                   SET @NextDayOvrlapMsg = 'You cannot have shifts with overlapping hours. '
				                  + ISNULL(@UserDisplayName,'')+' finishes a shift on '
				                  + FORMAT(@DutyEndTime,'dd/MM/yyyy HH:mm')
								  + ' and starts the next shift on '
								  +FORMAT(@NextStartTime,'dd/MM/yyyy HH:mm')
								  + '. You must change the hours so that these shifts do not overlap. '
				 END


        INSERT INTO @StatusMessage
		SELECT  @OverLapStatus,
			    @NextDayOvrlapMsg
				


	RETURN;

 END