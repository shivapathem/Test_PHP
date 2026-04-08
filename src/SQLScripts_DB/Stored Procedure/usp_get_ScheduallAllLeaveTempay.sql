USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ScheduallAllLeaveTempay]    Script Date: 2/10/2021 11:32:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_ScheduallAllLeaveTempay]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_get_ScheduallAllLeaveTempay] 
	-- Add the parameters for the stored procedure here
	@intTeamID int,
	@startdate varchar (50),
	@enddate varchar (50)
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

   		
SELECT          Teampay_Aux.dbo.SchedJobs.ExtStaffNumber, Teampay_Aux.dbo.REF_SchedEventTypes.TypeID, 
                               DATEDIFF(minute, Teampay_Aux.dbo.SchedJobs.StartDateTime, Teampay_Aux.dbo.SchedJobs.EndDateTime) AS Duration, Teampay_Aux.dbo.SchedJobs.JobDate
               FROM            Teampay_Aux.dbo.SchedJobs 
               INNER JOIN      Teampay_Aux.dbo.REF_SchedEventTypes ON Teampay_Aux.dbo.SchedJobs.JobType = Teampay_Aux.dbo.REF_SchedEventTypes.TypeID AND Teampay_Aux.dbo.SchedJobs.SystemID = Teampay_Aux.dbo.REF_SchedEventTypes.SystemID 
               INNER JOIN      StaffDetails sd ON Teampay_Aux.dbo.SchedJobs.ExtStaffNumber = sd.StaffNumber
               INNER JOIN ScheduledPeople sp on sp.StaffDetailsID = sd.StaffID 
															INNER JOIN ScheduledPersonTeam_LINK spl on spl.ScheduledPersonID = sp.ScheduledPersonID and  spl.IsHomeTeam = 1  and convert(datetime,EndDate,110) >= convert(datetime,convert(varchar,GETDATE(),110),110)
								  		and (
								  					convert(datetime, StartDate, 110) >= convert(datetime, convert(varchar,GETDATE(),110), 110) or
								  					isnull(convert(datetime,EndDate,110),''9999-01-01'') >= convert(datetime,convert(varchar(30),getdate(),110),110)
								  					and convert(datetime,StartDate,110) <= convert(datetime,convert(varchar(30),getdate(),110),110))
			  WHERE           (Teampay_Aux.dbo.REF_SchedEventTypes.IsLeave = 1) 
               AND             (spl.TeamID = @intTeamID) 
               AND             (Teampay_Aux.dbo.SchedJobs.JobDate >= CONVERT(DATETIME, @startdate, 102)) 
               AND             (Teampay_Aux.dbo.SchedJobs.JobDate <= CONVERT(DATETIME,@enddate, 102))
               ORDER BY        Teampay_Aux.dbo.SchedJobs.ExtStaffNumber, Teampay_Aux.dbo.SchedJobs.JobDate	

						
END'
EXEC dbo.sp_executesql @strSQL

GO
