USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetAdminLeaveRequestGroupsFromLogin]    Script Date: 26/10/2025 17:31:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER      PROCEDURE  [dbo].[usp_GetAdminLeaveRequestGroupsFromLogin]
	@strUser varchar(50),
	@divadminStatus varchar(1),
	@systemadminStatus varchar(1)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	Declare @strQuery VARCHAR(MAX);

    -- Insert statements for procedure here 
    -- IF(@systemadminStatus='1' OR @divadminStatus='1') 
    IF(@systemadminStatus='1') 
    BEGIN
        SET @strQuery = '
        SELECT Description, 
               HoursPerLeaveDay, 
               ShowLeaveOverLimit, 
               ExtraLeaveClicks, 
               SummerLeaveOverLimit, 
               RequestsAllowedMonthly, 
               RequestsAllowedYearly, 
               email, 
               emailcopiesto, 
               ID, 
               ''2'' as Admin,
               IsPartDayLeaveAllowed,
               LRG.DivisionID,
               DIV.DivisionName,
               allowEmails
        FROM LeaveRequestGroups LRG (nolock) 
        LEFT JOIN Divisions DIV ON DIV.DivisionID = LRG.DivisionID'
    END
	ELSE IF(@divadminStatus='1'  AND @systemadminStatus <> '1' )
    BEGIN
        SET @strQuery = '
        SELECT distinct Description, 
               HoursPerLeaveDay, 
               ShowLeaveOverLimit, 
               ExtraLeaveClicks, 
               SummerLeaveOverLimit, 
               RequestsAllowedMonthly, 
               RequestsAllowedYearly, 
               email, 
               emailcopiesto, 
               ID, 
               ''2'' as Admin,
               IsPartDayLeaveAllowed,
               LRG.DivisionID,
               DIV.DivisionName,
               allowEmails
        FROM LeaveRequestGroups LRG (nolock) 
        INNER JOIN Divisions DIV ON DIV.DivisionID = LRG.DivisionID 
        INNER JOIN UserRoles DA ON DA.UR_DivisionId = DIV.DivisionID 
		INNER JOIN UserDetails us on us.UD_UserID = da.UR_UserID 
		inner join REF_Roles rr on rr.RoleID = da.UR_RoleID
		WHERE rr.RoleName = ''Area Admin''
		  and us.ud_netlogin  = '''+@strUser+'''
		  and GETDATE() between UR_StartDate and UR_EndDate
	  UNION 
       SELECT distinct Description, 
               HoursPerLeaveDay, 
               ShowLeaveOverLimit, 
               ExtraLeaveClicks, 
               SummerLeaveOverLimit, 
               RequestsAllowedMonthly, 
               RequestsAllowedYearly, 
               email, 
               emailcopiesto, 
               ID, 
               ''2'' as Admin,
               IsPartDayLeaveAllowed,
               LRG.DivisionID,
               NULL as DivisionName,
               allowEmails
        FROM LeaveRequestGroups LRG (nolock) 
	    WHERE LRG.DivisionID IS NULL
	  '
    END
    ELSE IF(@divadminStatus <> '1' and @systemadminStatus <> '1'  ) 
    BEGIN

	 SET @strQuery ='SELECT LRG.Description, 
	                        LRG.HoursPerLeaveDay, 
							LRG.ShowLeaveOverLimit, 
							LRG.ExtraLeaveClicks,
							LRG.SummerLeaveOverLimit,
							LRG.RequestsAllowedMonthly, 
							LRG.RequestsAllowedYearly, 
							LRG.email, 
							LRG.emailcopiesto,
							LRG.ID, 
							SWC.Admin,
							LRG.IsPartDayLeaveAllowed,
							LRG.DivisionID,
							DIV.DivisionName,
							LRG.allowEmails
                      FROM Staff_Web_Config_LeaveGroups_Link (nolock) SWC
                     INNER JOIN LeaveRequestGroups (nolock) LRG ON SWC.LeaveGroupID = LRG.ID
					 LEFT JOIN Divisions DIV on DIV.DivisionID = LRG.DivisionID
                     WHERE (SWC.Login = '''+@strUser+''') 
					   AND (SWC.Admin >= 1) 
					   AND (SWC.IsActive = 1)';

    END
    EXEC(@strQuery);
END