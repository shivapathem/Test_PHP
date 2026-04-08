USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_CreateUpdateSchedulingTeam]    Script Date: 24/12/2025 22:42:40 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER       PROCEDURE [dbo].[usp_CreateUpdateSchedulingTeam]
	-- Add the parameters for the stored procedure here
	@schedulingTeamName varchar(120),
	@schedulingTeamDescription nvarchar(500),
	@divisionId int,
	@isActive int,
	@defaultSicknessHoursAllocation int,
	@defaultDutyDuration int,
	@workTimeDirectiveOptOut bit,
	@checkOverSixDaysWorked bit,
	@checkOverFiveDaysWorked bit,
	@signIn bit,
	@signInDays int,
	@allowInBuilding bit,
	@allowOvertimeRequests bit,
	@colourWeek bit,
	@locks bit,
	@locksStart int,
	@locksEnd int,
	@locksWeekataTime bit,
	@maskType varchar(50),
	@maskAfter int,
	@DailyViewMasking bit,
	@dailyViewMaskingDays int,
	@freelancerMasking bit,
	@freelancerMaskingDays int,
	@restrictedEditing bit,
	@numberofDaysAllowedEditing int,
	@editingStart int,
	@editingEnd int,
	@WeekendOnly bit,
	@autoLockTodayTimer bit,
	@hasGridChecks bit,
	@showProductionView bit,
	@autoImportWeeks bit,
	@NoofAutoAutoimportWeeks varchar(50),
	@hasXmasPoints bit,
	@defaultNumberweeksRotaPattern int,
	@defaultRotaStartDate varchar(50),
	@currentLeaveYear int,
	@leaveSelectiveHide bit,
	@hasHandovers bit,
	@staffAvailabilityReportStartDate varchar(50),
	@task varchar(20),
	@tab varchar(20),
	@intuserid int,
	@intnewteamid int,
	@email varchar(100),
	@defaultActiveCode INT,
	@IsRestrictCopyDuty BIT,
	@showJobsInWeeklyView BIT,
	@IsCreateDutyFromRota BIT,
	@IsShowEditYearly BIT, 
	@IsRestrictDeleteDuty BIT, 
	@RestrictApplyROTAPattern BIT

AS
BEGIN

	set @defaultRotaStartDate = convert(datetime,@defaultRotaStartDate,103);
	set @staffAvailabilityReportStartDate = convert(datetime,@staffAvailabilityReportStartDate,103);

	SET NOCOUNT ON;

	declare @err int, @rows int, @intstatus int, @strreturnstring varchar(200), @intScheduledTeamID int,
	@schedulingTeamNameprevvalue varchar(100),@schedulingTeamDescriptionprevvalue nvarchar(max), @divisionIdprevvalue int,@isActiveprevvalue int,
	@defaultSicknessHoursAllocationprevvalue int,@defaultDutyDurationprevvalue int,
	@maskTypeprevvalue varchar(50),@maskAfterprevvalue int,@DailyViewMaskingprevvalue bit,
	@dailyViewMaskingDaysprevvalue int,@freelancerMaskingprevvalue bit,@freelancerMaskingDaysprevvalue int,@restrictedEditingprevvalue bit,
	@numberofDaysAllowedEditingprevvalue int,@WeekendOnlyprevvalue bit,@autoLockTodayTimerprevvalue bit,@workTimeDirectiveOptOutprevvalue bit,
	@checkOverSixDaysWorkedprevvalue bit,@checkOverFiveDaysWorkedprevvalue bit,@locksprevvalue bit,@locksStartprevvalue int,@locksWeekataTimeprevvalue bit,
	@locksEndprevvalue int,@signInprevvalue bit,@signInDaysprevvalue int,@allowInBuildingprevvalue bit,@hasGridChecksprevvalue bit,@autoImportWeeksprevvalue bit,
	@NoofAutoAutoimportWeeksprevvalue varchar(100),@showProductionViewprevvalue bit,@allowOvertimeRequestsprevvalue bit,@colourWeekprevvalue bit,
	@defaultNumberweeksRotaPatternprevvalue int,@defaultRotaStartDateprevvalue datetime,@currentLeaveYearprevvalue int,@leaveSelectiveHideprevvalue bit,
	@hasHandoversprevvalue bit,@hasXmasPointsprevvalue bit,@staffAvailabilityReportStartDateprevvalue datetime,@editingStartprevvalue int,
	@editingEndprevvalue int, @emailprevvalue varchar(100),
	@history nvarchar(max), @HistoryType int, @UserName varchar(100),@divisionNameprevvalue varchar(200),
	@IsRestrictCopyDutyprevvalue BIT,
	@divisionName varchar(200),@defaultActiveCodeprevvalue int, @showJobsInWeeklyViewprevvalue BIT, @IsCreateDutyFromRotaPrevValue BIT,
	@IsShowEditYearlyprevvalue BIT, 	@IsRestrictDeleteDutyprevvalue BIT, @RestrictApplyROTAPatternprevvalue BIT;

	set @intstatus = 1;
	set @rows = 0;
	set @err = 0;
	set @strreturnstring = 'success';
	set @intScheduledTeamID = 0;
	set @history = '';
	set @HistoryType = 3;

	IF (@tab = 'identity')
	BEGIN
		IF ( ISNULL(@divisionId,0 ) <= 0 )
		 BEGIN
			Set @strreturnstring =  'Area Name for Scheduling team '+@schedulingTeamName+' is missing.';
			SET @intstatus = 0;
 
			select @intstatus intStatus, @strreturnstring strstatusschteam, @intnewteamid intnewidschteam;
			Return 0				  
 
		 END
	
		IF EXISTS (select 1 from schedulingTeams (NOLOCK) st
			where st.schedulingTeamName = @schedulingTeamName and st.schedulingTeamId != @intnewteamid and st.divisionid = @divisionId)
		BEGIN
			Set @strreturnstring =  'This Scheduling Team Name Already Exists, Please Try with Other Scheduling Team Name.';
			SET @intstatus = 0;

			select @intstatus intStatus, @strreturnstring strstatusschteam, @intnewteamid intnewidschteam;
			Return 0
		END
	END

	select
		@UserName = UD_DisplayName
	from UserDetails as u
	where u.UD_UserID = @intuserid

	IF (rtrim(ltrim(@UserName))='') SET @UserName= (Select UD_NetLogin from UserDetails where UD_UserID=@intuserid)
    -- Insert statements for procedure here
	IF (@task = 'create')
	BEGIN
		IF (@tab = 'identity')
		BEGIN
			set @history = 'Created by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
			+ CONVERT(VARCHAR(5),getdate(),108)  +'.<br>Identity tab record inserted.<br>';
		END
		ELSE IF (@tab = 'Allocations')
		BEGIN
			set @history = 'Created by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
			+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br> Allocation tab record inserted.<br>';
		END
		ELSE
		BEGIN
			set @history = 'Created by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
			+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br> Miscellaneous tab record inserted.<br>';
		END
	END
	ELSE
	BEGIN
		select @schedulingTeamNameprevvalue = st.schedulingTeamName,@schedulingTeamDescriptionprevvalue = st.schedulingTeamDescription,@divisionIdprevvalue = st.divisionid,
			@isActiveprevvalue = st.isActive,@defaultSicknessHoursAllocationprevvalue = st.defaultSicknessHoursAllocation,
			@defaultDutyDurationprevvalue = st.defaultDutyDuration,@maskTypeprevvalue = st.maskType,@maskAfterprevvalue = st.maskAfter,@DailyViewMaskingprevvalue = st.DailyViewMasking,
			@dailyViewMaskingDaysprevvalue = st.dailyViewMaskingDays,@freelancerMaskingprevvalue = st.freelancerMasking,@freelancerMaskingDaysprevvalue = st.freelancerMaskingDays,
			@restrictedEditingprevvalue = st.restrictedEditing,@numberofDaysAllowedEditingprevvalue = st.numberofDaysAllowedEditing,@WeekendOnlyprevvalue = st.WeekendOnly,
			@autoLockTodayTimerprevvalue = st.autoLockTodayTimer,@workTimeDirectiveOptOutprevvalue = st.workTimeDirectiveOptOut,@checkOverSixDaysWorkedprevvalue = st.checkOverSixDaysWorked,
			@checkOverFiveDaysWorkedprevvalue = st.checkOverFiveDaysWorked,@locksprevvalue = st.locks,@locksStartprevvalue = st.locksStart,@locksWeekataTimeprevvalue = st.locksWeekataTime,
			@locksEndprevvalue = st.locksEnd,@signInprevvalue = st.signIn,@signInDaysprevvalue = st.signInDays,@allowInBuildingprevvalue = st.allowInBuilding,
			@hasGridChecksprevvalue = st.hasGridChecks,@autoImportWeeksprevvalue = st.autoImportWeeks,@NoofAutoAutoimportWeeksprevvalue = st.NoofAutoAutoimportWeeks,
			@showProductionViewprevvalue = st.showProductionView,@allowOvertimeRequestsprevvalue = st.allowOvertimeRequests,@colourWeekprevvalue = st.colourWeek,
			@defaultNumberweeksRotaPatternprevvalue = st.defaultNumberweeksRotaPattern,@defaultRotaStartDateprevvalue = st.defaultRotaStartDate,
			@currentLeaveYearprevvalue = st.currentLeaveYear,@leaveSelectiveHideprevvalue = st.leaveSelectiveHide,@hasHandoversprevvalue = st.hasHandovers,
			@hasXmasPointsprevvalue = st.hasXmasPoints,@staffAvailabilityReportStartDateprevvalue = st.staffAvailabilityReportStartDate,
			@editingStartprevvalue = st.editingStart,@editingEndprevvalue = st.editingEnd,@emailprevvalue = st.Email, 
			@IsRestrictCopyDutyprevvalue = st.IsRestrictCopyDuty, @showJobsInWeeklyViewprevvalue = st.ShowJobsInWeeklyView,
			@IsCreateDutyFromRotaPrevValue = IsCreateDutyFromRota, @IsShowEditYearlyprevvalue = @IsShowEditYearly, 	
			@IsRestrictDeleteDutyprevvalue = @IsRestrictDeleteDuty, @RestrictApplyROTAPatternprevvalue = @RestrictApplyROTAPattern
		from schedulingTeams as st
		where st.schedulingTeamId = @intnewteamid

		IF (@tab = 'identity')
		BEGIN
			IF(@schedulingTeamNameprevvalue != @schedulingTeamName)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) 
				+ ' at ' + CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Scheduling Team Name changed from "' 
				+ @schedulingTeamNameprevvalue + '" to "' + @schedulingTeamName + '".<br>';
			END
			IF(@schedulingTeamDescriptionprevvalue != @schedulingTeamDescription)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) 
				+ ' at ' + CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Scheduling Team Description changed from "' 
				+ @schedulingTeamDescriptionprevvalue + '" to "' + @schedulingTeamDescription + '".<br>';
			END
			IF(@divisionIdprevvalue != @divisionId)
			BEGIN
				select @divisionNameprevvalue = DivisionName from Divisions (NOLOCK) where DivisionID = @divisionIdprevvalue;
				select @divisionName = DivisionName from Divisions (NOLOCK) where DivisionID = @divisionId;
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) 
				+ ' at ' + CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Scheduling Team Description changed from "' 
				+ @divisionNameprevvalue + '" to "' + @divisionName + '".<br>';
			END
			IF(@defaultActiveCodeprevvalue != @defaultActiveCode)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) 
				+ ' at ' + CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Default Duty Charge Code changed from "' 
				+ @defaultActiveCodeprevvalue + '" to "'+@defaultActiveCode + '".<br>';
			END
			IF(@isActiveprevvalue != @isActive)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) 
				+ ' at ' + CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Scheduling Team Status changed from "' 
				+ case when @isActiveprevvalue = 1 then 'Yes' else 'No' end + '" to "' 
				+ case when @isActive = 1 then 'Yes' else 'No' end + '".<br>';
			END
		END
		ELSE IF (@tab = 'Allocations')
		BEGIN
			IF(@defaultSicknessHoursAllocationprevvalue != @defaultSicknessHoursAllocation)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) 
				+ ' at ' + CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Default Sickness hours for Allocation changed from "' 
				+ case when @defaultSicknessHoursAllocationprevvalue = 1 then '7 Hours' when @defaultSicknessHoursAllocationprevvalue = 2 
				then 'Standard Day length from teampay config' when @defaultSicknessHoursAllocationprevvalue = 3 
				then 'Shift Length (without mealbreaks)' else 'N/A' end 
				+ '" to "' + case when @defaultSicknessHoursAllocation = 1 then '7 Hours' 
				when @defaultSicknessHoursAllocation = 2 then 'Standard Day length from teampay config' 
				when @defaultSicknessHoursAllocation = 3 then 'Shift Length (without mealbreaks)' else 'N/A' end + '".<br>';
			END
			IF(@defaultDutyDurationprevvalue != @defaultDutyDuration)
			BEGIN
			set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
			+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Default Duty Duration (Exc Break) changed from "'  
			+ right('0'+CAST( isnull( @defaultDutyDurationprevvalue,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull( @defaultDutyDurationprevvalue,0) % 3600)/60 AS varchar(2)),2) 
									  + '" to "' + right('0'+CAST( isnull(@defaultDutyDuration,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@defaultDutyDuration,0) % 3600)/60 AS varchar(2)),2)+ '".<br>';
			END
			IF(@maskTypeprevvalue != @maskType)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Mask Type changed from "' 
				+ case when @maskTypeprevvalue = 0 then 'Hide All Allocations' 
				when @maskTypeprevvalue = 1 then 'Show First Letter' when @maskTypeprevvalue = 2 
				then 'Show All Allocations' else 'N/A' end 
				+ '" to "' + case when @maskType = 0 then 'Hide All Allocations' 
				when @maskType = 1 then 'Show First Letter' when @maskType = 2 
				then 'Show All Allocations' else 'N/A' end + '".<br>';
			END
			IF(@maskAfterprevvalue != @maskAfter)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) 
				+ '.<br>The Mask After changed from "' + convert(varchar(50), @maskAfterprevvalue) + '" to "' 
				+ convert(varchar(50), @maskAfter) + '".<br>';
			END
			IF(@DailyViewMaskingprevvalue != @DailyViewMasking)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Daily View Masking changed from "' 
				+ case when @DailyViewMaskingprevvalue = 1 then 'Yes' else 'No' end + '" to "' 
				+ case when @DailyViewMasking = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@dailyViewMaskingDaysprevvalue != @dailyViewMaskingDays)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Daily View Masking Days changed from "' 
				+ convert(varchar(50), @dailyViewMaskingDaysprevvalue) + '" to "' + convert(varchar(50), @dailyViewMaskingDays) + '".<br>';
			END
			IF(@freelancerMaskingprevvalue != @freelancerMasking)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Freelancer Masking changed from "' 
				+ case when @freelancerMaskingprevvalue = 1 then 'Yes' else 'No' end + ' to '
				+case when @freelancerMasking = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@freelancerMaskingDaysprevvalue != @freelancerMaskingDays)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  +'.<br>The Freelancer Masking Days changed from "' 
				+ convert(varchar(50), @freelancerMaskingDaysprevvalue) + '" to "' + convert(varchar(50), @freelancerMaskingDays) + '".<br>';
			END
			IF(@restrictedEditingprevvalue != @restrictedEditing)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Time Restricted changed from "' 
				+ case when @restrictedEditingprevvalue = 1 then 'Yes' else 'No' end + '" to "' 
				+ case when @restrictedEditing = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@numberofDaysAllowedEditingprevvalue != @numberofDaysAllowedEditing)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Number of Days Allowed for Editing changed from "' 
				+ convert(varchar(50), @numberofDaysAllowedEditingprevvalue) + '" to "' 
				+ convert(varchar(50), @numberofDaysAllowedEditing) + '".<br>';
			END
			IF(@WeekendOnlyprevvalue != @WeekendOnly)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Weekend Only changed from "'
				+ case when @WeekendOnlyprevvalue = 1 then 'Yes' else 'No' end +'" to "' 
				+ case when @WeekendOnly = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@autoLockTodayTimerprevvalue != @autoLockTodayTimer)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Lock Current Day on Timer changed from "'
				+ case when @autoLockTodayTimerprevvalue = 1 then 'Yes' else 'No' end +'" to "'
				+ case when @autoLockTodayTimer = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@workTimeDirectiveOptOutprevvalue != @workTimeDirectiveOptOut)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  +'.<br>The Work Time Directive Opt Out changed from "'
				+case when @workTimeDirectiveOptOutprevvalue = 1 then 'Yes' else 'No' end +'" to "' 
				+ case when @workTimeDirectiveOptOut = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@checkOverSixDaysWorkedprevvalue != @checkOverSixDaysWorked)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  +'.<br>The Check Over Six Days Worked changed from "'
				+ case when @checkOverSixDaysWorkedprevvalue = 1 then 'Yes' else 'No' end + '" to "' 
				+ case when @checkOverSixDaysWorked = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@checkOverFiveDaysWorkedprevvalue != @checkOverFiveDaysWorked)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Check Over Five Days Worked changed from "' 
				+ case when @checkOverFiveDaysWorkedprevvalue = 1 then 'Yes' else 'No' end + '" to "' 
				+ case when @checkOverFiveDaysWorked = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@locksprevvalue != @locks)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Locks changed from "' 
				+ case when @locksprevvalue = 1 then 'Yes' else 'No' end + '" to "' + case when @locks = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@locksStartprevvalue != @locksStart)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Locks Start changed from "' 
				+ convert(varchar(50), @locksStartprevvalue) + '" to "' + convert(varchar(50), @locksStart) + '".<br>';
			END
			IF(@locksWeekataTimeprevvalue != @locksWeekataTime)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Locks Week At a Time changed from "' 
				+ case when @locksWeekataTimeprevvalue = 1 then 'Yes' else 'No' end + '" to "' 
				+ case when @locksWeekataTime = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@locksEndprevvalue != @locksEnd)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Locks End changed from "' 
				+ convert(varchar(50), @locksEndprevvalue) + '" to "' + convert(varchar(50), @locksEnd) + '".<br>';
			END
			IF(@signInprevvalue != @signIn)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  +'.<br>The Sign In changed from "' 
				+ case when @signInprevvalue = 1 then 'Yes' else 'No' end + '" to "'
				+ case when @signIn = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@signInDaysprevvalue != @signInDays)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Sign-In Days changed from "' 
				+ convert(varchar(50), @signInDaysprevvalue) + '" to "' + convert(varchar(50), @signInDays) + '".<br>';
			END
			IF(@allowInBuildingprevvalue != @allowInBuilding)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Allow In Building Sign-in changed from "' 
				+ case when @allowInBuildingprevvalue = 1 then 'Yes' else 'No' end + '" to "' 
				+ case when @allowInBuilding = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@hasGridChecksprevvalue != @hasGridChecks)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + '.<br>The Show Grid Checks changed from "' 
				+ case when @hasGridChecksprevvalue = 1 then 'Yes' else 'No' end + '" to "' 
				+ case when @hasGridChecks = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@autoImportWeeksprevvalue != @autoImportWeeks)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Auto Import Weeks changed from "'
				+ case when @autoImportWeeksprevvalue = 1 then 'Yes' else 'No' end + '" to "' 
				+ case when @autoImportWeeks = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@NoofAutoAutoimportWeeksprevvalue != @NoofAutoAutoimportWeeks)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) 
				+ ' at ' + CONVERT(VARCHAR(5),getdate(),108)  
				+ '.<br>The No Of Auto Import Weeks changed from "' + convert(varchar(50), @NoofAutoAutoimportWeeksprevvalue) 
				+ '" to "' + convert(varchar(50), @NoofAutoAutoimportWeeks) + '".<br>';
			END


			IF(@IsRestrictCopyDutyprevvalue != @IsRestrictCopyDuty)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' + CONVERT(VARCHAR(5),getdate(),108)  
				+ '.<br> Restrict copy duty flag changed from "' 
				+ case when @IsRestrictCopyDutyprevvalue = 1 then 'Yes' else 'No' end + '" to "' 
				+ case when @IsRestrictCopyDuty = 1 then 'Yes' else 'No' end + '".<br>';
			END

			IF(@showProductionViewprevvalue != @showProductionView)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Show Production View changed from "' 
				+ case when @showProductionViewprevvalue = 1 then 'Yes' else 'No' end + '" to "' + case when @showProductionView = 1 then 'Yes' else 'No' end + '".<br>';
			END

			IF(@IsCreateDutyFromRotaPrevValue != @IsCreateDutyFromRota)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) 
				+ ' at ' + CONVERT(VARCHAR(5),getdate(),108)  + '.<br>Create Duty From Rotta Pattern changed from "' 
				+ case when @IsCreateDutyFromRotaPrevValue = 1 then 'Yes' else 'No' end + '" to "' 
				+ case when @IsCreateDutyFromRota = 1 then 'Yes' else 'No' end + '".<br>';
			END

			IF(@showJobsInWeeklyViewprevvalue != @showJobsInWeeklyView)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Show Jobs In Weekly View changed from "' 
				+ case when @showJobsInWeeklyViewprevvalue = 1 then 'Yes' else 'No' end + '" to "' + case when @showJobsInWeeklyView = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@allowOvertimeRequestsprevvalue != @allowOvertimeRequests)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  +'.<br>The Allow Overtime Requests changed from "' 
				+ case when @allowOvertimeRequestsprevvalue = 1 then 'Yes' else 'No' end + '" to "' + case when @allowOvertimeRequests = 1 then 'Yes' else 'No' end + '".<br>';
			END

			IF(@IsShowEditYearlyprevvalue != @IsShowEditYearly)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  +'.<br>The show edit yearly changed from "' 
				+ case when @IsShowEditYearlyprevvalue = 1 then 'Yes' else 'No' end 
				+ '" to "' + case when @IsShowEditYearly = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@IsRestrictDeleteDutyprevvalue <> @IsRestrictDeleteDuty)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  +'.<br>The allow delete duty changed from "' 
				+ case when @IsRestrictDeleteDutyprevvalue = 1 then 'Yes' else 'No' end 
				+ '" to "' + case when @IsRestrictDeleteDuty = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@RestrictApplyROTAPatternprevvalue != @RestrictApplyROTAPattern)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  +'.<br>The restrict apply ROTA pattern changed from "' 
				+ case when @RestrictApplyROTAPatternprevvalue = 1 then 'Yes' else 'No' end 
				+ '" to "' + case when @RestrictApplyROTAPattern = 1 then 'Yes' else 'No' end + '".<br>';
			END


			IF(@colourWeekprevvalue != @colourWeek)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
				+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Colour cells on Weekly/Monthly Views changed from "' 
				+ case when @colourWeekprevvalue = 1 then 'Yes' else 'No' end + '" to "' + case when @colourWeek = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@editingStartprevvalue != @editingStart)
			BEGIN
			set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' 
			+ CONVERT(VARCHAR(5),getdate(),108)  + '.<br>The Editing Start changed from "'  + right('0'
			+CAST( isnull(@editingStartprevvalue,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@editingStartprevvalue,0) % 3600)/60 AS varchar(2)),2) 
									  + '" to "' +  right('0'+CAST( isnull(@editingStart,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@editingStart,0) % 3600)/60 AS varchar(2)),2)+ '".<br>';
			END
			IF(@editingEndprevvalue != @editingEnd)
			BEGIN
			set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' + CONVERT(VARCHAR(5),getdate(),108)  
			+ '.<br>The Editing End changed from "'  + right('0'+CAST( isnull( @editingEndprevvalue,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull( @editingEndprevvalue,0) % 3600)/60 AS varchar(2)),2) + '" to "' 
									  +  right('0'+CAST( isnull(@editingEnd,0) / 3600 AS varchar(2)),2) + ':'  
									  + right('0' + CAST( (isnull(@editingEnd,0) % 3600)/60 AS varchar(2)),2)+ '".<br>';
			END
		END
		ELSE
		BEGIN
			IF(@defaultNumberweeksRotaPatternprevvalue != @defaultNumberweeksRotaPattern)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' + CONVERT(VARCHAR(5),getdate(),108)  
				+ '.<br>The Default Number of weeks in Rota Pattern changed from "' + convert(varchar(50), @defaultNumberweeksRotaPatternprevvalue)
				+ '" to "' + convert(varchar(50), @defaultNumberweeksRotaPattern) + '".<br>';
			END
			IF(@defaultRotaStartDateprevvalue != @defaultRotaStartDate)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' + CONVERT(VARCHAR(5),getdate(),108) 
				+'.<br>The Default Rota Start Date changed from "' + convert(varchar(50), @defaultRotaStartDateprevvalue, 105) + '" to "' 
				+ convert(varchar(50), convert(datetime,@defaultRotaStartDate,105),105) + '".<br>';
			END
			IF(@currentLeaveYearprevvalue != @currentLeaveYear)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' + CONVERT(VARCHAR(5),getdate(),108)  
				+ '.<br>The Current Leave Year changed from "' + convert(varchar(50), @currentLeaveYearprevvalue) + '" to "' + convert(varchar(50), @currentLeaveYear) + '".<br>';
			END
			IF(@leaveSelectiveHideprevvalue != @leaveSelectiveHide)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' + CONVERT(VARCHAR(5),getdate(),108) 
				+ '.<br>The Selectively Hide Leave changed from "' + case when @leaveSelectiveHideprevvalue = 1 then 'Yes' else 'No' end + '" to "'
				+ case when @leaveSelectiveHide = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@hasHandoversprevvalue != @hasHandovers)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' + CONVERT(VARCHAR(5),getdate(),108)  
				+ '.<br>The Show Handovers changed from "' + case when @hasHandoversprevvalue = 1 then 'Yes' else 'No' end + '" to "' 
				+ case when @hasHandovers = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@hasXmasPointsprevvalue != @hasXmasPoints)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' + CONVERT(VARCHAR(5),getdate(),108) 
				+ '.<br>The Show Christmas Points changed from "' + case when @hasXmasPointsprevvalue = 1 then 'Yes' else 'No' end + '" to "'
				+ case when @hasXmasPoints = 1 then 'Yes' else 'No' end + '".<br>';
			END
			IF(@staffAvailabilityReportStartDateprevvalue != @staffAvailabilityReportStartDate)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' + CONVERT(VARCHAR(5),getdate(),108)  
				+ ' at ' + CONVERT(VARCHAR(5),getdate(),108)  +'.<br>The Staff Availability Report Start Date changed from "' 
				+ convert(varchar(50), @staffAvailabilityReportStartDateprevvalue, 105) + '" to "' 
				+ convert(varchar(50), convert(datetime,@staffAvailabilityReportStartDate,105), 105) + '".<br>';
			END
			IF(@emailprevvalue != @email)
			BEGIN
				set @history += 'Record updated by '+@UserName+' on ' + convert(varchar, getdate(),105) + ' at ' + CONVERT(VARCHAR(5),getdate(),108)  
				+ '.<br>The Email changed from "' + @emailprevvalue + '" to "' + @email + '".<br>';
			END
		END
	END
	IF (@tab = 'identity')
	BEGIN
		IF ( ISNULL(@divisionId,0 ) <= 0 )
		 BEGIN
			Set @strreturnstring =  'Area Name for Scheduling team '+@schedulingTeamName+' is missing.';
			SET @intstatus = 0;
 
			select @intstatus intStatus, @strreturnstring strstatusschteam, @intnewteamid intnewidschteam;
			Return 0				  
 
		 END


		if (@intnewteamid = 0)
		BEGIN
			IF EXISTS(select 1 from SchedulingTeams (NOLOCK) st --inner join schedulingTeamDivision_Link std on st.schedulingTeamId = std.schedulingTeamId
						where st.schedulingTeamName = @schedulingTeamName and st.divisionid = @divisionId)
			BEGIN
				set @intstatus = 0;
				set @strreturnString = 'Scheduling team name: '+@schedulingTeamName+' is already present in the system. Please use a unique name.';
				select @intstatus intStatus, @strreturnstring  strstatusschteam, @intnewteamid intnewidschteam;
				return;
			END
			BEGIN Transaction
				insert into Schedulingteams (divisionid,schedulingTeamName,schedulingTeamDescription,isActive,defaultActiveCode,createdby,createddate,
				defaultSicknessHoursAllocation,defaultDutyDuration,maskAfter,defaultNumberweeksRotaPattern,defaultRotaStartDate,currentLeaveYear,
				staffAvailabilityReportStartDate,IsRestrictCopyDuty)
					Values (@divisionId,@schedulingTeamName,@schedulingTeamDescription, @isActive,@defaultActiveCode,@intuserid,getdate(),
					@defaultSicknessHoursAllocation,@defaultDutyDuration,@maskAfter,@defaultNumberweeksRotaPattern,@defaultRotaStartDate,@currentLeaveYear,
					@staffAvailabilityReportStartDate,@IsRestrictCopyDuty);
				SELECT @intnewteamid = SCOPE_IDENTITY();

				SELECT @err = @@ERROR, @rows = @@ROWCOUNT
				IF @err <> 0
				BEGIN
					ROLLBACK TRANSACTION
					SET @intstatus = 0;
					SET @strreturnstring = 'There was an error inserting the record. Please contact administrator.';
					select @intstatus intStatus, @strreturnstring strstatusschteam, @intnewteamid intnewidschteam;
					RETURN;
				END
				IF @rows = 0
				BEGIN
					ROLLBACK TRANSACTION
					SET @intstatus = 0;
					SET @strreturnstring = 'There was an error inserting the record. Please contact administrator.';
					select @intstatus intStatus, @strreturnstring strstatusschteam, @intnewteamid intnewidschteam;
					RETURN;
				END


				-- Insert record into history
			IF (ISNULL(@history,'') <> '')
			 BEGIN
				insert into [dbo].[History] (HistoryType,UserID,History,datetime,AttributeID)
					values (@HistoryType,@intuserid,@history,getdate(),@intnewteamid)
			 END
			 
			COMMIT TRANSACTION;
			select @intstatus intStatus, 'Identity Tab record inserted successfully.' strstatusschteam, @intnewteamid intnewidschteam;
			return;
		END
		ELSE
		BEGIN
			IF EXISTS(select 1 
			           from Schedulingteams (NOLOCK) st 
						where st.schedulingTeamName = @schedulingTeamName 
						and st.divisionid = @divisionId 
						and st.schedulingTeamId != @intnewteamid)
			BEGIN
				set @intstatus = 0;
				set @strreturnString = 'Scheduling team name: '+@schedulingTeamName+' is already present in the system. Please use a unique name.';
				select @intstatus intStatus, @strreturnstring  strstatusschteam, @intnewteamid intnewidschteam;
				return;
			END
			BEGIN TRANSACTION
				update Schedulingteams
					set schedulingTeamName = @schedulingTeamName,
						divisionid=@divisionId,
						schedulingTeamDescription = @schedulingTeamDescription,
						defaultActiveCode = @defaultActiveCode,
						isActive = @isActive,
						modifiedby = @intuserid,
						modifieddate = getdate()
				where schedulingTeamId = @intnewteamid;
				SELECT @err = @@ERROR, @rows = @@ROWCOUNT
				IF @err <> 0
				BEGIN
					ROLLBACK TRANSACTION
					SET @intstatus = 0;
					SET @strreturnstring = 'There was an error inserting the record. Please contact administrator.';
					select @intstatus intStatus, @strreturnstring strstatusschteam, @intnewteamid intnewidschteam;
					RETURN;
				END
				IF @rows = 0
				BEGIN
					ROLLBACK TRANSACTION
					SET @intstatus = 0;
					SET @strreturnstring = 'There was an error inserting the record. Please contact administrator.';
					select @intstatus intStatus, @strreturnstring strstatusschteam, @intnewteamid intnewidschteam;
					RETURN;
				END


				IF (@isActive = 0)
				BEGIN
				  delete from Exported_rota where SchedulingTeamId = @intnewteamid;
				  update MasterRotas set IsExported=0 where TeamID = @intnewteamid;
				END

				-- Insert record into history
				IF (ISNULL(@history,'') <> '')
				 BEGIN				
				    insert into [dbo].[History] (HistoryType,UserID,History,datetime,AttributeID)
					values (@HistoryType,@intuserid,@history,getdate(),@intnewteamid)
				 END
				
			commit TRANSACTION
			select @intstatus intStatus, 'Identity Tab record updated successfully.' strstatusschteam, @intnewteamid intnewidschteam;
			return;
		END
	END
	ELSE IF (@tab = 'allocations')
	BEGIN
		BEGIN TRANSACTION
			update Schedulingteams
				set defaultSicknessHoursAllocation = @defaultSicknessHoursAllocation,
					--divisionid=@divisionId,
					defaultDutyDuration = @defaultDutyDuration,
					maskType = @maskType,
					maskAfter = @maskAfter,
					DailyViewMasking = @DailyViewMasking,
					dailyViewMaskingDays = @dailyViewMaskingDays,
					freelancerMasking = @freelancerMasking,
					freelancerMaskingDays = @freelancerMaskingDays,
					restrictedEditing = @restrictedEditing,
					numberofDaysAllowedEditing = @numberofDaysAllowedEditing,
					WeekendOnly = @WeekendOnly,
					editingStart = @editingStart,
					autoLockTodayTimer = @autoLockTodayTimer,
					editingEnd = @editingEnd,
					workTimeDirectiveOptOut = @workTimeDirectiveOptOut,
					checkOverSixDaysWorked = @checkOverSixDaysWorked,
					checkOverFiveDaysWorked = @checkOverFiveDaysWorked,
					locks = @locks,
					locksStart = @locksStart,
					locksWeekataTime = @locksWeekataTime,
					locksEnd = @locksEnd,
					signIn = @signIn,
					signInDays = @signInDays,
					allowInBuilding = @allowInBuilding,
					hasGridChecks = @hasGridChecks,
					autoImportWeeks = @autoImportWeeks,
					NoofAutoAutoimportWeeks = @NoofAutoAutoimportWeeks,
					showProductionView = @showProductionView,
					allowOvertimeRequests = @allowOvertimeRequests,
					colourWeek = @colourWeek,
					IsRestrictCopyDuty = @IsRestrictCopyDuty,
					modifiedby = @intuserid,
					modifieddate = getdate(),
					ShowJobsInWeeklyView = @showJobsInWeeklyView,
					IsCreateDutyFromRota = @IsCreateDutyFromRota,
					IsShowEditYearly = @IsShowEditYearly , 	
			        IsRestrictDeleteDuty = @IsRestrictDeleteDuty, 
					RestrictApplyROTAPattern = @RestrictApplyROTAPattern
				where schedulingTeamId = @intnewteamid


			SELECT @err = @@ERROR, @rows = @@ROWCOUNT
			IF @err <> 0
			BEGIN
				ROLLBACK TRANSACTION
				SET @intstatus = 0;
				SET @strreturnstring = 'There was an error inserting the record. Please contact administrator.';
				select @intstatus intStatus, @strreturnstring strstatusschteam, @intnewteamid intnewidschteam;
			RETURN;
			END
			IF @rows = 0
			BEGIN
				ROLLBACK TRANSACTION
				SET @intstatus = 0;
				SET @strreturnstring = 'There was an error inserting the record. Please contact administrator.';
				select @intstatus intStatus, @strreturnstring strstatusschteam, @intnewteamid intnewidschteam;
				RETURN;
			END
			
			-- Insert record into history
			IF (ISNULL(@history,'') <> '')
			  BEGIN				
				insert into [dbo].[History] (HistoryType,UserID,History,datetime,AttributeID)
				values (@HistoryType,@intuserid,@history,getdate(),@intnewteamid)
			  END
			  
		COMMIT TRANSACTION
		select @intstatus intStatus, 'Allocations Tab record inserted successfully.' strstatusschteam, @intnewteamid intnewidschteam;
		return;
	END
	ELSE
	BEGIN
		BEGIN TRANSACTION
			update Schedulingteams
				set defaultNumberweeksRotaPattern = @defaultNumberweeksRotaPattern,
					defaultRotaStartDate = @defaultRotaStartDate,
					currentLeaveYear = @currentLeaveYear,
					leaveSelectiveHide = @leaveSelectiveHide,
					hasHandovers = @hasHandovers,
					hasXmasPoints = @hasXmasPoints,
					staffAvailabilityReportStartDate = @staffAvailabilityReportStartDate,
					modifiedby = @intuserid,
					modifieddate = getdate(),
					Email = @email
			where schedulingTeamId = @intnewteamid

			SELECT @err = @@ERROR, @rows = @@ROWCOUNT
			IF @err <> 0
			BEGIN
				ROLLBACK TRANSACTION
				SET @intstatus = 0;
				SET @strreturnstring = 'There was an error inserting the record. Please contact administrator.';
				select @intstatus intStatus, @strreturnstring strstatusschteam, @intnewteamid intnewidschteam;
				RETURN;
			END
			IF @rows = 0
			BEGIN
				ROLLBACK TRANSACTION
				SET @intstatus = 0;
				SET @strreturnstring = 'There was an error inserting the record. Please contact administrator.';
				select @intstatus intStatus, @strreturnstring strstatusschteam, @intnewteamid intnewidschteam;
				RETURN;
			END
			-- Insert record into history
			
			IF (ISNULL(@history,'') <> '')
			 BEGIN				
				insert into [dbo].[History] (HistoryType,UserID,History,datetime,AttributeID)
				values (@HistoryType,@intuserid,@history,getdate(),@intnewteamid)
			 END
			 
		COMMIT TRANSACTION
		
		select @intstatus intStatus, 'Miscellaneous Tab record inserted successfully.' strstatusschteam, @intnewteamid intnewidschteam;
		return;
	END

END