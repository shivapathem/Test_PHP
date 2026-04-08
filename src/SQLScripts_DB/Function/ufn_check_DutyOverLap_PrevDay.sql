USE [BBCSchedules]
GO
/****** Object:  UserDefinedFunction [dbo].[ufn_check_DutyOverLap_PrevDay]    Script Date: 10/07/2025 12:49:21 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER   FUNCTION [dbo].[ufn_check_DutyOverLap_PrevDay]
(
	@AllocationsSPID	INT,
	@DutyStartTime		DATETIME
)
RETURNS @StatusMessage  TABLE  
            ( 
			   IsDutyOverLap	  BIT,
			   PrevDayOvrlapMsg     NVARCHAR(500)
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

		SELECT @PrevEndTime = ( SELECT  CASE WHEN ISNULL(ET.AD_DutyEndTimeLocal,@DutyStartTime) > @DutyStartTime 
											 THEN ET.AD_DutyEndTimeLocal 
											 ELSE NULL END AS PrevEndTime
				FROM AllocationsDuties ET (nolock)
			   INNER JOIN AllocationsScheduledPersons ETS on ETS.ASP_AllocationsDutyID = ET.AD_AllocationsDutyID
			   WHERE ETS.ASP_SchedulingPersonID = ASP.ASP_SchedulingPersonID
				 AND ETS.ASP_DutyDate = DATEADD(DAY,-1,AD.AD_DutyDate)
				 AND ET.AD_DutyType NOT IN (7,8,9)),
			@UserDisplayName = UD_DisplayName
		FROM AllocationsDuties AD (nolock)
	   INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsDutyID = AD.AD_AllocationsDutyID
	   INNER JOIN UserDetails UD on UD.UD_USerID = ASP.ASP_SchedulingPersonID
	   WHERE ASP.ASP_AllocationsSPID = @AllocationsSPID

				IF ( @PrevEndTime IS NOT NULL  )
				 BEGIN
				   SET @OverLapStatus = 1
                   SET @PrevDayOvrlapMsg = 'You cannot have shifts with overlapping hours. '
				                  + ISNULL(@UserDisplayName,'')+' finishes a shift on '
				                  + FORMAT(@PrevEndTime,'dd/MM/yyyy HH:mm')
								  + ' and starts the next shift on '
								  +FORMAT(@DutyStartTime,'dd/MM/yyyy HH:mm')
								  + '. You must change the hours so that these shifts do not overlap. '
				 END

        INSERT INTO @StatusMessage
		SELECT  @OverLapStatus,
				@PrevDayOvrlapMsg			

	RETURN;

 END